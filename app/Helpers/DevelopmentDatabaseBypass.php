<?php

// ==========================================
// DEVELOPMENT MODE - BYPASS DATABASE SETUP
// ==========================================
// Este archivo proporciona autenticación temporal mientras configuramos la BD

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\User;

// Override de la guard de autenticación para desarrollo sin BD
if (app()->environment('local') && !auth()->check()) {
    Auth::viaRequest('dev-bypass', function ($request) {
        // Si se intenta login
        if ($request->path() === 'login' && $request->isMethod('post')) {
            $username = $request->input('username');
            $password = $request->input('password');
            
            // Credenciales de demo
            if ($username === 'admin' && $password === '123456') {
                // Crear usuario fake
                $user = new User([
                    'id' => 1,
                    'name' => 'Administrador Demo',
                    'username' => 'admin',
                    'email' => 'admin@demo.local',
                    'business_id' => 1,
                    'user_type' => 'admin',
                    'status' => 'active',
                    'allow_login' => true,
                ]);
                
                Auth::login($user, true);
                return $user;
            }
        }
        
        // Si hay usuario en sesión, devolverlo
        if ($request->session()->has('dev_user')) {
            $user = new User([
                'id' => 1,
                'name' => 'Administrador Demo',
                'username' => 'admin',
                'email' => 'admin@demo.local',
                'business_id' => 1,
                'user_type' => 'admin',
                'status' => 'active',
                'allow_login' => true,
            ]);
            return $user;
        }
        
        return null;
    });
}
