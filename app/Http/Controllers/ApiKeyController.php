<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiKeyController extends Controller
{
    /**
     * Show API management dashboard
     */
    public function index()
    {
        $businessId = Auth::user()->business_id;

        $apiKeys = ApiKey::where('business_id', $businessId)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        $permissions = ApiKey::availablePermissions();

        // Recent logs
        $recentLogs = ApiLog::whereIn('api_key_id', $apiKeys->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Stats
        $stats = [
            'total_keys' => $apiKeys->count(),
            'active_keys' => $apiKeys->where('is_active', true)->count(),
            'total_requests_today' => ApiLog::whereIn('api_key_id', $apiKeys->pluck('id'))
                ->whereDate('created_at', today())
                ->count(),
            'errors_today' => ApiLog::whereIn('api_key_id', $apiKeys->pluck('id'))
                ->whereDate('created_at', today())
                ->where('response_code', '>=', 400)
                ->count(),
        ];

        return view('api_management.index', compact('apiKeys', 'permissions', 'recentLogs', 'stats'));
    }

    /**
     * Store a new API key
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array|min:1',
            'allowed_ips' => 'nullable|string',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $businessId = Auth::user()->business_id;
        $keyPair = ApiKey::generateKeyPair();

        // Parse allowed IPs
        $allowedIps = null;
        if (!empty($request->allowed_ips)) {
            $allowedIps = array_map('trim', explode(',', $request->allowed_ips));
            $allowedIps = array_filter($allowedIps);
        }

        $apiKey = ApiKey::create([
            'business_id' => $businessId,
            'created_by' => Auth::id(),
            'name' => $request->name,
            'api_key' => $keyPair['api_key'],
            'api_secret' => $keyPair['api_secret'],
            'permissions' => $request->permissions,
            'allowed_ips' => $allowedIps,
            'is_active' => true,
            'expires_at' => $request->expires_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'API Key creada exitosamente. ¡Guarde el Secret, no se mostrará de nuevo!',
            'data' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'api_key' => $keyPair['api_key'],
                'api_secret' => $keyPair['api_secret'], // Only shown once!
            ],
        ]);
    }

    /**
     * Toggle active/inactive
     */
    public function toggle(Request $request, $id)
    {
        $businessId = Auth::user()->business_id;
        $apiKey = ApiKey::where('business_id', $businessId)->findOrFail($id);

        $apiKey->update(['is_active' => !$apiKey->is_active]);

        return response()->json([
            'success' => true,
            'message' => $apiKey->is_active ? 'API Key activada.' : 'API Key desactivada.',
            'is_active' => $apiKey->is_active,
        ]);
    }

    /**
     * Update permissions and settings
     */
    public function update(Request $request, $id)
    {
        $businessId = Auth::user()->business_id;
        $apiKey = ApiKey::where('business_id', $businessId)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array|min:1',
            'allowed_ips' => 'nullable|string',
            'expires_at' => 'nullable|date',
        ]);

        $allowedIps = null;
        if (!empty($request->allowed_ips)) {
            $allowedIps = array_map('trim', explode(',', $request->allowed_ips));
            $allowedIps = array_filter($allowedIps);
        }

        $apiKey->update([
            'name' => $request->name,
            'permissions' => $request->permissions,
            'allowed_ips' => $allowedIps,
            'expires_at' => $request->expires_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'API Key actualizada exitosamente.',
        ]);
    }

    /**
     * Regenerate key pair
     */
    public function regenerate($id)
    {
        $businessId = Auth::user()->business_id;
        $apiKey = ApiKey::where('business_id', $businessId)->findOrFail($id);

        $keyPair = ApiKey::generateKeyPair();
        $apiKey->update([
            'api_key' => $keyPair['api_key'],
            'api_secret' => $keyPair['api_secret'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Claves regeneradas. ¡Guarde el nuevo Secret!',
            'data' => [
                'api_key' => $keyPair['api_key'],
                'api_secret' => $keyPair['api_secret'],
            ],
        ]);
    }

    /**
     * Delete an API key
     */
    public function destroy($id)
    {
        $businessId = Auth::user()->business_id;
        $apiKey = ApiKey::where('business_id', $businessId)->findOrFail($id);

        $apiKey->delete();

        return response()->json([
            'success' => true,
            'message' => 'API Key eliminada exitosamente.',
        ]);
    }

    /**
     * Get logs for a specific API key
     */
    public function logs($id)
    {
        $businessId = Auth::user()->business_id;
        $apiKey = ApiKey::where('business_id', $businessId)->findOrFail($id);

        $logs = ApiLog::where('api_key_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * API Documentation page
     */
    public function docs()
    {
        return view('api_management.docs');
    }
}
