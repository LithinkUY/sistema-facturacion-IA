<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;

/**
 * Usuario fake para desarrollo sin base de datos
 */
class DemoUser implements Authenticatable
{
    use AuthenticatableTrait;

    public $id = 1;
    public $name = 'Admin Demo';
    public $username = 'admin';
    public $email = 'admin@demo.local';
    public $password = '123456';
    public $business_id = 1;
    public $user_type = 'admin';
    public $status = 'active';
    public $allow_login = true;
    public $remember_token = null;
    public $timestamps = false;

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    // Implementar métodos requeridos por Authenticatable
    public function getKeyName()
    {
        return 'id';
    }

    public function getKey()
    {
        return $this->id;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Save the model (no-op for demo user)
     */
    public function save()
    {
        // No-op: Demo user doesn't persist to database
        return true;
    }
}
