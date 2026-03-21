<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'buy_rate' => 'float',
        'sell_rate' => 'float',
        'rate_date' => 'date',
    ];

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    /**
     * Get the latest exchange rate for a given currency code and business
     */
    public static function getLatestRate($business_id, $currency_code = 'USD')
    {
        return self::where('business_id', $business_id)
            ->where('currency_code', $currency_code)
            ->orderBy('rate_date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->first();
    }
}
