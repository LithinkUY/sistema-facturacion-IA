<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Product;
use App\Variation;
use App\VariationLocationDetails;
use App\Contact;
use App\Transaction;
use App\TransactionSellLine;
use App\Category;
use App\Brands;
use App\Business;
use App\BusinessLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductApiController extends Controller
{
    /**
     * GET /api/v1/products
     * List products with filters and pagination
     */
    public function index(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $query = Product::where('business_id', $businessId)
            ->with(['brand', 'category', 'sub_category', 'unit', 'product_tax'])
            ->where('type', '!=', 'modifier');

        // Filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->has('sku')) {
            $query->where('sku', 'like', '%' . $request->sku . '%');
        }
        if ($request->has('active')) {
            $query->where('is_inactive', $request->active == '1' ? 0 : 1);
        }
        if ($request->has('updated_since')) {
            $query->where('updated_at', '>=', $request->updated_since);
        }

        $perPage = min($request->get('per_page', 25), 100);
        $products = $query->paginate($perPage);

        // Transform
        $products->getCollection()->transform(function ($product) use ($businessId) {
            return $this->transformProduct($product, $businessId);
        });

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/products/{id}
     * Show a single product
     */
    public function show(Request $request, $id)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $product = Product::where('business_id', $businessId)
            ->with(['brand', 'category', 'sub_category', 'unit', 'product_tax', 'product_variations.variations'])
            ->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => 'Producto no encontrado.',
                'code' => 'NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformProduct($product, $businessId, true),
        ]);
    }

    /**
     * POST /api/v1/products
     * Create a new product
     */
    public function store(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'type' => 'in:single,variable,combo',
            'unit_id' => 'nullable|integer',
            'brand_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'selling_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'tax_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'barcode_type' => 'in:C128,C39,EAN13,EAN8,UPCA,UPCE',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::create([
                'name' => $request->name,
                'business_id' => $businessId,
                'type' => $request->get('type', 'single'),
                'unit_id' => $request->unit_id,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
                'tax' => $request->tax_id,
                'sku' => $request->sku ?? $this->generateSku($businessId),
                'alert_quantity' => $request->get('alert_quantity', 0),
                'barcode_type' => $request->get('barcode_type', 'C128'),
                'product_description' => $request->description,
                'created_by' => $apiKey->created_by,
                'is_inactive' => 0,
                'enable_stock' => $request->get('enable_stock', 1),
            ]);

            // Create default variation
            $variation = $product->product_variations()->create([
                'variation_template_id' => null,
                'name' => 'DUMMY',
            ]);

            $variation->variations()->create([
                'name' => 'DUMMY',
                'product_id' => $product->id,
                'sub_sku' => $product->sku,
                'default_purchase_price' => $request->get('purchase_price', 0),
                'dpp_inc_tax' => $request->get('purchase_price', 0),
                'profit_percent' => 0,
                'default_sell_price' => $request->selling_price,
                'sell_price_inc_tax' => $request->selling_price,
                'is_dummy' => 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente.',
                'data' => $this->transformProduct($product->fresh(['brand', 'category', 'unit']), $businessId),
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Error al crear producto: ' . $e->getMessage(),
                'code' => 'CREATE_ERROR',
            ], 500);
        }
    }

    /**
     * PUT /api/v1/products/{id}
     * Update a product
     */
    public function update(Request $request, $id)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $product = Product::where('business_id', $businessId)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => 'Producto no encontrado.',
                'code' => 'NOT_FOUND',
            ], 404);
        }

        $updateData = [];
        $fillable = ['name', 'brand_id', 'category_id', 'unit_id', 'alert_quantity', 'barcode_type'];

        foreach ($fillable as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->$field;
            }
        }

        if ($request->has('description')) {
            $updateData['product_description'] = $request->description;
        }
        if ($request->has('tax_id')) {
            $updateData['tax'] = $request->tax_id;
        }
        if ($request->has('active')) {
            $updateData['is_inactive'] = $request->active ? 0 : 1;
        }

        try {
            DB::beginTransaction();

            $product->update($updateData);

            // Update prices if provided
            if ($request->has('selling_price') || $request->has('purchase_price')) {
                $defaultVariation = Variation::where('product_id', $product->id)
                    ->where('is_dummy', 1)
                    ->first();

                if ($defaultVariation) {
                    $priceUpdate = [];
                    if ($request->has('selling_price')) {
                        $priceUpdate['default_sell_price'] = $request->selling_price;
                        $priceUpdate['sell_price_inc_tax'] = $request->selling_price;
                    }
                    if ($request->has('purchase_price')) {
                        $priceUpdate['default_purchase_price'] = $request->purchase_price;
                        $priceUpdate['dpp_inc_tax'] = $request->purchase_price;
                    }
                    $defaultVariation->update($priceUpdate);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado exitosamente.',
                'data' => $this->transformProduct($product->fresh(['brand', 'category', 'unit']), $businessId),
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar: ' . $e->getMessage(),
                'code' => 'UPDATE_ERROR',
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/products/{id}
     */
    public function destroy(Request $request, $id)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $product = Product::where('business_id', $businessId)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => 'Producto no encontrado.',
                'code' => 'NOT_FOUND',
            ], 404);
        }

        try {
            $product->delete();
            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar: ' . $e->getMessage(),
                'code' => 'DELETE_ERROR',
            ], 500);
        }
    }

    /**
     * GET /api/v1/products/{id}/stock
     * Get stock levels for a product
     */
    public function stock(Request $request, $id)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $product = Product::where('business_id', $businessId)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => 'Producto no encontrado.',
                'code' => 'NOT_FOUND',
            ], 404);
        }

        $stock = VariationLocationDetails::join('variations', 'variations.id', '=', 'variation_location_details.variation_id')
            ->join('business_locations', 'business_locations.id', '=', 'variation_location_details.location_id')
            ->where('variations.product_id', $id)
            ->select(
                'business_locations.name as location_name',
                'business_locations.id as location_id',
                'variation_location_details.qty_available',
                'variations.sub_sku as sku'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'product_id' => (int)$id,
                'product_name' => $product->name,
                'stock_by_location' => $stock,
                'total_stock' => $stock->sum('qty_available'),
            ],
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────

    private function transformProduct($product, $businessId, $includeVariations = false)
    {
        $defaultVariation = Variation::where('product_id', $product->id)
            ->where('is_dummy', 1)
            ->first();

        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'type' => $product->type,
            'description' => $product->product_description,
            'brand' => $product->brand ? ['id' => $product->brand->id, 'name' => $product->brand->name] : null,
            'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
            'sub_category' => $product->sub_category ? ['id' => $product->sub_category->id, 'name' => $product->sub_category->name] : null,
            'unit' => $product->unit ? ['id' => $product->unit->id, 'name' => $product->unit->actual_name] : null,
            'selling_price' => $defaultVariation ? (float)$defaultVariation->default_sell_price : 0,
            'selling_price_inc_tax' => $defaultVariation ? (float)$defaultVariation->sell_price_inc_tax : 0,
            'purchase_price' => $defaultVariation ? (float)$defaultVariation->default_purchase_price : 0,
            'alert_quantity' => (float)$product->alert_quantity,
            'active' => $product->is_inactive == 0,
            'image_url' => $product->image_url,
            'created_at' => $product->created_at ? $product->created_at->toIso8601String() : null,
            'updated_at' => $product->updated_at ? $product->updated_at->toIso8601String() : null,
        ];

        if ($includeVariations && $product->product_variations) {
            $data['variations'] = [];
            foreach ($product->product_variations as $pv) {
                foreach ($pv->variations as $v) {
                    $data['variations'][] = [
                        'id' => $v->id,
                        'name' => $v->name,
                        'sub_sku' => $v->sub_sku,
                        'selling_price' => (float)$v->default_sell_price,
                        'purchase_price' => (float)$v->default_purchase_price,
                    ];
                }
            }
        }

        return $data;
    }

    private function generateSku($businessId)
    {
        $count = Product::where('business_id', $businessId)->count() + 1;
        return 'SKU-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
