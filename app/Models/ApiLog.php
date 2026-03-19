<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function apiKey()
    {
        return $this->belongsTo(ApiKey::class);
    }

    /**
     * Log an API request
     */
    public static function logRequest(
        ?int $apiKeyId,
        string $method,
        string $endpoint,
        ?string $ip,
        int $responseCode,
        ?string $requestBody = null,
        ?int $responseTimeMs = null
    ): self {
        return self::create([
            'api_key_id' => $apiKeyId,
            'method' => $method,
            'endpoint' => $endpoint,
            'ip_address' => $ip,
            'response_code' => $responseCode,
            'request_body' => $requestBody,
            'response_time_ms' => $responseTimeMs,
            'created_at' => now(),
        ]);
    }
}
