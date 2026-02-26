<?php

return [
    'enabled' => env('CFE_ENABLED', false),
    'auto_submit' => env('CFE_AUTO_SUBMIT', true),
    'environment' => env('CFE_ENVIRONMENT', 'testing'),
    'default_cfe_type' => (int) env('CFE_DEFAULT_TYPE', 111),
    'default_series' => env('CFE_DEFAULT_SERIES', 'A'),
    'emitter_rut' => env('CFE_EMITTER_RUT'),
    'dgi_user_rut' => env('CFE_DGI_USER_RUT'),
    'certificate_path' => env('CFE_CERT_PATH'),
    'certificate_password' => env('CFE_CERT_PASSWORD'),
    'http_timeout' => env('CFE_HTTP_TIMEOUT', 30),
    'verify_ssl' => env('CFE_VERIFY_SSL', false),
];
