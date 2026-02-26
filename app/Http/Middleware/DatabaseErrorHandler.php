<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Database\PDOException;

class DatabaseErrorHandler
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (QueryException $e) {
            if (app()->environment('local')) {
                // En desarrollo, si hay error de driver/BD, retornar respuesta genérica
                if (strpos($e->getMessage(), 'could not find driver') !== false ||
                    strpos($e->getMessage(), 'SQLSTATE') !== false) {
                    return response()->json([
                        'error' => 'Database connection unavailable in development mode',
                        'data' => []
                    ], 200); // Retornar 200 para no romper el flujo
                }
            }
            throw $e;
        } catch (\Exception $e) {
            if (app()->environment('local') && strpos($e->getMessage(), 'could not find driver') !== false) {
                return response()->json([
                    'error' => 'Database connection unavailable',
                    'data' => []
                ], 200);
            }
            throw $e;
        }
    }
}
