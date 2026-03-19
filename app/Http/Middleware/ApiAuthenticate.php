<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;

class ApiAuthenticate
{
    /**
     * Handle an incoming API request.
     * Authenticates via X-API-KEY header.
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $startTime = microtime(true);

        // If already authenticated in this request (nested middleware), just check permissions
        $apiKey = $request->attributes->get('api_key');
        if ($apiKey) {
            // Already authenticated, just check additional permissions
            if (!empty($permissions)) {
                foreach ($permissions as $permission) {
                    if (!$apiKey->hasPermission($permission)) {
                        $this->logRequest($apiKey->id, $request, 403);
                        return response()->json([
                            'success' => false,
                            'error' => "Permiso requerido: {$permission}",
                            'code' => 'INSUFFICIENT_PERMISSIONS',
                        ], 403);
                    }
                }
            }
            return $next($request);
        }

        $apiKeyString = $request->header('X-API-KEY');

        if (empty($apiKeyString)) {
            $this->logRequest(null, $request, 401);
            return response()->json([
                'success' => false,
                'error' => 'API key requerida. Envíe el header X-API-KEY.',
                'code' => 'MISSING_API_KEY',
            ], 401);
        }

        $apiKey = ApiKey::where('api_key', $apiKeyString)->first();

        if (!$apiKey) {
            $this->logRequest(null, $request, 401);
            return response()->json([
                'success' => false,
                'error' => 'API key inválida.',
                'code' => 'INVALID_API_KEY',
            ], 401);
        }

        // Validate key is active, not expired, IP allowed
        if (!$apiKey->isValid($request->ip())) {
            $this->logRequest($apiKey->id, $request, 403);
            return response()->json([
                'success' => false,
                'error' => 'API key desactivada, expirada o IP no permitida.',
                'code' => 'API_KEY_INACTIVE',
            ], 403);
        }

        // Check specific permissions if required
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                if (!$apiKey->hasPermission($permission)) {
                    $this->logRequest($apiKey->id, $request, 403);
                    return response()->json([
                        'success' => false,
                        'error' => "Permiso requerido: {$permission}",
                        'code' => 'INSUFFICIENT_PERMISSIONS',
                    ], 403);
                }
            }
        }

        // Record usage
        $apiKey->recordUsage();

        // Attach apiKey to request for use in controllers
        $request->merge(['_api_key' => $apiKey]);
        $request->attributes->set('api_key', $apiKey);

        $response = $next($request);

        // Log successful request
        $endTime = microtime(true);
        $responseTimeMs = (int)(($endTime - $startTime) * 1000);
        $this->logRequest($apiKey->id, $request, $response->getStatusCode(), $responseTimeMs);

        return $response;
    }

    private function logRequest(?int $apiKeyId, Request $request, int $statusCode, ?int $responseTimeMs = null): void
    {
        try {
            ApiLog::logRequest(
                $apiKeyId,
                $request->method(),
                $request->path(),
                $request->ip(),
                $statusCode,
                $request->method() !== 'GET' ? json_encode($request->except(['_api_key', 'password'])) : null,
                $responseTimeMs
            );
        } catch (\Exception $e) {
            \Log::error('Error logging API request: ' . $e->getMessage());
        }
    }
}
