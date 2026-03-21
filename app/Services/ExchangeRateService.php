<?php

namespace App\Services;

use App\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * Get the current USD exchange rate for a business.
     * Uses cache (30 min), then DB, then fetches from API.
     */
    public static function getUsdRate($business_id)
    {
        $cacheKey = "exchange_rate_usd_{$business_id}";

        return Cache::remember($cacheKey, 1800, function () use ($business_id) {
            // First try to get today's rate from DB
            $today = now()->toDateString();
            $rate = ExchangeRate::where('business_id', $business_id)
                ->where('currency_code', 'USD')
                ->where('rate_date', $today)
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($rate) {
                return [
                    'buy' => (float) $rate->buy_rate,
                    'sell' => (float) $rate->sell_rate,
                    'source' => $rate->source,
                    'date' => $rate->rate_date->format('Y-m-d'),
                    'updated_at' => $rate->updated_at->format('H:i'),
                ];
            }

            // If no rate today, try to fetch from API
            $fetched = self::fetchFromBrou();
            if ($fetched) {
                // Save to DB
                ExchangeRate::create([
                    'business_id' => $business_id,
                    'currency_code' => 'USD',
                    'buy_rate' => $fetched['buy'],
                    'sell_rate' => $fetched['sell'],
                    'source' => 'brou',
                    'rate_date' => $today,
                ]);

                return [
                    'buy' => $fetched['buy'],
                    'sell' => $fetched['sell'],
                    'source' => 'brou',
                    'date' => $today,
                    'updated_at' => now()->format('H:i'),
                ];
            }

            // Fallback: get the most recent rate from DB (any date)
            $lastRate = ExchangeRate::getLatestRate($business_id, 'USD');
            if ($lastRate) {
                return [
                    'buy' => (float) $lastRate->buy_rate,
                    'sell' => (float) $lastRate->sell_rate,
                    'source' => $lastRate->source . ' (anterior)',
                    'date' => $lastRate->rate_date->format('Y-m-d'),
                    'updated_at' => $lastRate->updated_at->format('H:i'),
                ];
            }

            // No rate at all - return null
            return null;
        });
    }

    /**
     * Fetch exchange rate from BROU (Banco República) API
     */
    public static function fetchFromBrou()
    {
        try {
            // BROU public API endpoint
            $response = Http::timeout(10)
                ->withOptions(['verify' => false])
                ->get('https://cotizaciones-brou.herokuapp.com/api/currency/latest');

            if ($response->successful()) {
                $data = $response->json();

                // The API returns rates array
                if (isset($data['rates'])) {
                    foreach ($data['rates'] as $rate) {
                        if (isset($rate['name']) && stripos($rate['name'], 'DOLAR') !== false && stripos($rate['name'], 'EBAY') === false) {
                            return [
                                'buy' => (float) ($rate['buy'] ?? 0),
                                'sell' => (float) ($rate['sell'] ?? 0),
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('ExchangeRate BROU API error: ' . $e->getMessage());
        }

        // Try alternative: scrape from a simple API
        return self::fetchFromAlternativeApi();
    }

    /**
     * Alternative API for exchange rates
     */
    public static function fetchFromAlternativeApi()
    {
        try {
            // Try exchangerate-api.com (free tier)
            $response = Http::timeout(10)
                ->withOptions(['verify' => false])
                ->get('https://api.exchangerate-api.com/v4/latest/USD');

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['rates']['UYU'])) {
                    $uyuPerUsd = (float) $data['rates']['UYU'];
                    return [
                        'buy' => round($uyuPerUsd - 0.5, 2), // Approximate spread
                        'sell' => round($uyuPerUsd + 0.5, 2),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('ExchangeRate alternative API error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Manually set exchange rate
     */
    public static function setManualRate($business_id, $buy, $sell, $currency_code = 'USD')
    {
        $today = now()->toDateString();

        $rate = ExchangeRate::updateOrCreate(
            [
                'business_id' => $business_id,
                'currency_code' => $currency_code,
                'rate_date' => $today,
                'source' => 'manual',
            ],
            [
                'buy_rate' => $buy,
                'sell_rate' => $sell,
            ]
        );

        // Clear cache
        Cache::forget("exchange_rate_usd_{$business_id}");

        return $rate;
    }

    /**
     * Refresh rate from API (clear cache and re-fetch)
     */
    public static function refreshRate($business_id, $currency_code = 'USD')
    {
        Cache::forget("exchange_rate_{$currency_code}_{$business_id}");
        Cache::forget("exchange_rate_usd_{$business_id}");

        $fetched = self::fetchFromBrou();
        if (!$fetched) {
            $fetched = self::fetchFromAlternativeApi();
        }

        if ($fetched) {
            $today = now()->toDateString();
            ExchangeRate::updateOrCreate(
                [
                    'business_id' => $business_id,
                    'currency_code' => $currency_code,
                    'rate_date' => $today,
                    'source' => 'api',
                ],
                [
                    'buy_rate' => $fetched['buy'],
                    'sell_rate' => $fetched['sell'],
                ]
            );
        }

        return $fetched;
    }

    /**
     * Convert an amount from one currency to business currency (UYU)
     */
    public static function convertToBusinessCurrency($amount, $from_currency_code, $business_id)
    {
        if ($from_currency_code === 'UYU') {
            return $amount;
        }

        $rate = self::getUsdRate($business_id);
        if ($rate && $from_currency_code === 'USD') {
            // Use sell rate (what the business pays to buy USD equivalent)
            return round($amount * $rate['sell'], 2);
        }

        return $amount;
    }

    /**
     * Convert an amount from business currency (UYU) to another currency
     */
    public static function convertFromBusinessCurrency($amount, $to_currency_code, $business_id)
    {
        if ($to_currency_code === 'UYU') {
            return $amount;
        }

        $rate = self::getUsdRate($business_id);
        if ($rate && $to_currency_code === 'USD') {
            return round($amount / $rate['sell'], 2);
        }

        return $amount;
    }
}
