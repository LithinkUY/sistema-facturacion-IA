<?php

// =====================================================
// DEVELOPMENT DATABASE MOCK - Handle missing PDO drivers
// =====================================================

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class DatabaseMock
{
    private static $mockData = [];

    /**
     * Retorna datos mock para queries en desarrollo
     */
    public static function getMockData($query)
    {
        // Detectar qué tabla se está consultando
        if (strpos($query, 'currencies') !== false) {
            return collect([
                (object)['id' => 1, 'info' => 'USD - United States Dollar (USD)'],
                (object)['id' => 2, 'info' => 'UYU - Uruguay Peso (UYU)'],
                (object)['id' => 3, 'info' => 'EUR - Euro (EUR)'],
            ]);
        }
        
        if (strpos($query, 'users') !== false) {
            return collect([]);
        }
        
        if (strpos($query, 'business') !== false) {
            return collect([]);
        }
        
        return collect([]);
    }

    /**
     * Interceptar queries fallidas en desarrollo
     */
    public static function handle($exception, $query)
    {
        if (app()->environment('local')) {
            if (strpos($exception->getMessage(), 'could not find driver') !== false) {
                return self::getMockData($query);
            }
        }
        throw $exception;
    }
}
