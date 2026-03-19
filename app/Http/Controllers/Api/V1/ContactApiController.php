<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactApiController extends Controller
{
    /**
     * GET /api/v1/contacts
     * List contacts with filters
     */
    public function index(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $query = Contact::where('business_id', $businessId);

        // Filters
        if ($request->has('type')) {
            $query->where('type', $request->type); // customer, supplier, both
        }
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->has('mobile')) {
            $query->where('mobile', 'like', '%' . $request->mobile . '%');
        }
        if ($request->has('tax_number')) {
            $query->where('tax_number', 'like', '%' . $request->tax_number . '%');
        }
        if ($request->has('contact_id')) {
            $query->where('contact_id', $request->contact_id);
        }
        if ($request->has('active')) {
            $query->where('contact_status', $request->active == '1' ? 'active' : 'inactive');
        }
        if ($request->has('updated_since')) {
            $query->where('updated_at', '>=', $request->updated_since);
        }

        $perPage = min($request->get('per_page', 25), 100);
        $contacts = $query->paginate($perPage);

        $contacts->getCollection()->transform(function ($contact) {
            return $this->transformContact($contact);
        });

        return response()->json([
            'success' => true,
            'data' => $contacts->items(),
            'pagination' => [
                'current_page' => $contacts->currentPage(),
                'last_page' => $contacts->lastPage(),
                'per_page' => $contacts->perPage(),
                'total' => $contacts->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/contacts/{id}
     */
    public function show(Request $request, $id)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $contact = Contact::where('business_id', $businessId)->find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'error' => 'Contacto no encontrado.',
                'code' => 'NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformContact($contact, true),
        ]);
    }

    /**
     * POST /api/v1/contacts
     */
    public function store(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $request->validate([
            'type' => 'required|in:customer,supplier,both',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'custom_field1' => 'nullable|string|max:255',
            'custom_field2' => 'nullable|string|max:255',
            'custom_field3' => 'nullable|string|max:255',
            'custom_field4' => 'nullable|string|max:255',
        ]);

        try {
            // Generate contact_id
            $ref_count = DB::table('reference_counts')
                ->where('ref_type', $request->type)
                ->where('business_id', $businessId)
                ->first();

            $contactId = 'CO' . str_pad(($ref_count->ref_count ?? 0) + 1, 4, '0', STR_PAD_LEFT);

            $contact = Contact::create([
                'business_id' => $businessId,
                'type' => $request->type,
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->get('mobile', ''),
                'phone' => $request->phone,
                'tax_number' => $request->tax_number,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'address_line_1' => $request->address,
                'shipping_address' => $request->shipping_address,
                'contact_id' => $contactId,
                'custom_field1' => $request->custom_field1,
                'custom_field2' => $request->custom_field2,
                'custom_field3' => $request->custom_field3,
                'custom_field4' => $request->custom_field4,
                'created_by' => $apiKey->created_by,
                'contact_status' => 'active',
            ]);

            // Increment reference count
            DB::table('reference_counts')
                ->where('ref_type', $request->type)
                ->where('business_id', $businessId)
                ->increment('ref_count');

            return response()->json([
                'success' => true,
                'message' => 'Contacto creado exitosamente.',
                'data' => $this->transformContact($contact),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al crear contacto: ' . $e->getMessage(),
                'code' => 'CREATE_ERROR',
            ], 500);
        }
    }

    /**
     * PUT /api/v1/contacts/{id}
     */
    public function update(Request $request, $id)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $contact = Contact::where('business_id', $businessId)->find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'error' => 'Contacto no encontrado.',
                'code' => 'NOT_FOUND',
            ], 404);
        }

        $updateData = [];
        $fillable = ['name', 'email', 'mobile', 'phone', 'tax_number', 'city', 'state', 'country',
                      'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4'];

        foreach ($fillable as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->$field;
            }
        }

        if ($request->has('address')) {
            $updateData['address_line_1'] = $request->address;
        }
        if ($request->has('shipping_address')) {
            $updateData['shipping_address'] = $request->shipping_address;
        }
        if ($request->has('active')) {
            $updateData['contact_status'] = $request->active ? 'active' : 'inactive';
        }

        try {
            $contact->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Contacto actualizado exitosamente.',
                'data' => $this->transformContact($contact->fresh()),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar: ' . $e->getMessage(),
                'code' => 'UPDATE_ERROR',
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/contacts/{id}
     */
    public function destroy(Request $request, $id)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $contact = Contact::where('business_id', $businessId)->find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'error' => 'Contacto no encontrado.',
                'code' => 'NOT_FOUND',
            ], 404);
        }

        // Check if contact has transactions
        $hasTransactions = \App\Transaction::where('contact_id', $id)->exists();

        if ($hasTransactions) {
            return response()->json([
                'success' => false,
                'error' => 'No se puede eliminar: el contacto tiene transacciones asociadas.',
                'code' => 'HAS_DEPENDENCIES',
            ], 409);
        }

        try {
            $contact->delete();
            return response()->json([
                'success' => true,
                'message' => 'Contacto eliminado exitosamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar: ' . $e->getMessage(),
                'code' => 'DELETE_ERROR',
            ], 500);
        }
    }

    // ─── Helpers ──────────────────────────────────────────

    private function transformContact($contact, $detailed = false)
    {
        $data = [
            'id' => $contact->id,
            'type' => $contact->type,
            'contact_id' => $contact->contact_id,
            'name' => $contact->name,
            'email' => $contact->email,
            'mobile' => $contact->mobile,
            'phone' => $contact->phone,
            'tax_number' => $contact->tax_number,
            'active' => $contact->contact_status === 'active',
            'city' => $contact->city,
            'state' => $contact->state,
            'country' => $contact->country,
            'address' => $contact->address_line_1,
            'created_at' => $contact->created_at ? $contact->created_at->toIso8601String() : null,
            'updated_at' => $contact->updated_at ? $contact->updated_at->toIso8601String() : null,
        ];

        if ($detailed) {
            $data['shipping_address'] = $contact->shipping_address;
            $data['custom_field1'] = $contact->custom_field1;
            $data['custom_field2'] = $contact->custom_field2;
            $data['custom_field3'] = $contact->custom_field3;
            $data['custom_field4'] = $contact->custom_field4;
            $data['total_purchase'] = (float)($contact->total_purchase ?? 0);
            $data['total_purchase_return'] = (float)($contact->total_purchase_return ?? 0);
            $data['total_invoice'] = (float)($contact->total_invoice ?? 0);
            $data['total_sell_return'] = (float)($contact->total_sell_return ?? 0);
            $data['opening_balance'] = (float)($contact->opening_balance ?? 0);
        }

        return $data;
    }
}
