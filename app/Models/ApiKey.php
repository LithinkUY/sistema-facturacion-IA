<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'permissions' => 'array',
        'allowed_ips' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $hidden = ['api_secret'];

    /**
     * Generate a new API key pair
     */
    public static function generateKeyPair(): array
    {
        return [
            'api_key' => 'sk_' . Str::random(48),
            'api_secret' => 'ss_' . Str::random(48),
        ];
    }

    /**
     * Available permission scopes
     */
    public static function availablePermissions(): array
    {
        return [
            'products.read' => 'Ver productos',
            'products.write' => 'Crear/editar productos',
            'products.delete' => 'Eliminar productos',
            'contacts.read' => 'Ver contactos (clientes/proveedores)',
            'contacts.write' => 'Crear/editar contactos',
            'contacts.delete' => 'Eliminar contactos',
            'transactions.read' => 'Ver transacciones (ventas/compras)',
            'transactions.write' => 'Crear transacciones',
            'categories.read' => 'Ver categorías',
            'categories.write' => 'Crear/editar categorías',
            'brands.read' => 'Ver marcas',
            'brands.write' => 'Crear/editar marcas',
            'stock.read' => 'Ver stock/inventario',
            'reports.read' => 'Ver reportes',
            'webhooks.manage' => 'Gestionar webhooks',
        ];
    }

    /**
     * Check if this key has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return false;
        }
        return in_array($permission, $this->permissions);
    }

    /**
     * Check if this key is valid (active, not expired, IP allowed)
     */
    public function isValid(?string $ip = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($ip && !empty($this->allowed_ips) && !in_array($ip, $this->allowed_ips)) {
            return false;
        }

        return true;
    }

    /**
     * Record usage
     */
    public function recordUsage(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    // Relationships
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function logs()
    {
        return $this->hasMany(ApiLog::class);
    }
}
