<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Category;
use App\Brands;
use App\BusinessLocation;
use Illuminate\Http\Request;

class CatalogApiController extends Controller
{
    /**
     * GET /api/v1/categories
     */
    public function categories(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $query = Category::where('business_id', $businessId);

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->where('parent_id', 0); // Only top-level by default
        }

        $categories = $query->orderBy('name')->get()->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'short_code' => $cat->short_code,
                'description' => $cat->description,
                'parent_id' => $cat->parent_id,
                'subcategories' => $cat->sub_categories->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->name,
                        'short_code' => $sub->short_code,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * POST /api/v1/categories
     */
    public function storeCategory(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'short_code' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'business_id' => $businessId,
            'short_code' => $request->short_code,
            'description' => $request->description,
            'parent_id' => $request->get('parent_id', 0),
            'created_by' => $apiKey->created_by,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente.',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'short_code' => $category->short_code,
                'parent_id' => $category->parent_id,
            ],
        ], 201);
    }

    /**
     * GET /api/v1/brands
     */
    public function brands(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $brands = Brands::where('business_id', $businessId)
            ->orderBy('name')
            ->get()
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'description' => $brand->description,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    /**
     * POST /api/v1/brands
     */
    public function storeBrand(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $brand = Brands::create([
            'name' => $request->name,
            'business_id' => $businessId,
            'description' => $request->description,
            'created_by' => $apiKey->created_by,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Marca creada exitosamente.',
            'data' => [
                'id' => $brand->id,
                'name' => $brand->name,
            ],
        ], 201);
    }

    /**
     * GET /api/v1/locations
     */
    public function locations(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $locations = BusinessLocation::where('business_id', $businessId)
            ->get()
            ->map(function ($loc) {
                return [
                    'id' => $loc->id,
                    'name' => $loc->name,
                    'landmark' => $loc->landmark,
                    'city' => $loc->city,
                    'state' => $loc->state,
                    'country' => $loc->country,
                    'zip_code' => $loc->zip_code,
                    'mobile' => $loc->mobile,
                    'email' => $loc->email,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }
}
