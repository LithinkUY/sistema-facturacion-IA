<?php

namespace App\Http\Controllers;

use App\Services\ExchangeRateService;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    /**
     * Get current USD exchange rate (JSON API for AJAX)
     */
    public function getRate(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $rate = ExchangeRateService::getUsdRate($business_id);

        if ($rate) {
            return response()->json([
                'success' => true,
                'data' => $rate,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo obtener la cotización',
        ]);
    }

    /**
     * Refresh rate from API
     */
    public function refreshRate(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $rate = ExchangeRateService::refreshRate($business_id);

        if ($rate) {
            return response()->json([
                'success' => true,
                'data' => [
                    'buy' => $rate['buy'],
                    'sell' => $rate['sell'],
                    'source' => 'api',
                    'date' => now()->toDateString(),
                    'updated_at' => now()->format('H:i'),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo actualizar la cotización',
        ]);
    }

    /**
     * Set manual exchange rate
     */
    public function setManualRate(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $request->validate([
            'buy_rate' => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0',
        ]);

        $rate = ExchangeRateService::setManualRate(
            $business_id,
            $request->buy_rate,
            $request->sell_rate
        );

        return response()->json([
            'success' => true,
            'data' => [
                'buy' => (float) $rate->buy_rate,
                'sell' => (float) $rate->sell_rate,
                'source' => 'manual',
                'date' => $rate->rate_date->format('Y-m-d'),
                'updated_at' => $rate->updated_at->format('H:i'),
            ],
        ]);
    }
}
