<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class DevelopmentAuthBypass
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo en desarrollo, si hay un error de base de datos
        if (!Auth::check() && app()->environment('local')) {
            try {
                // Intentar autenticación normal
                return $next($request);
            } catch (\Exception $e) {
                // Si falla por base de datos, crear usuario fake
                if (strpos($e->getMessage(), 'could not find driver') !== false || 
                    strpos($e->getMessage(), 'SQLSTATE') !== false) {
                    
                    // Crear un usuario fake en memoria
                    $fakeUser = new User([
                        'id' => 1,
                        'name' => 'Admin Demo',
                        'username' => 'admin',
                        'email' => 'admin@demo.local',
                        'business_id' => 1,
                        'user_type' => 'admin',
                        'status' => 'active',
                        'allow_login' => true,
                    ]);
                    
                    // No guardar en base de datos, solo en sesión
                    Auth::login($fakeUser, true);
                }
                
                return $next($request);
            }
        }

        return $next($request);
    }
}
