<?php

$configuredOrigins = array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', ''))));

$defaultOrigins = array_filter([
    env('FRONTEND_URL', 'https://augepanamby.net.br'),
    'https://www.augepanamby.net.br',
    'http://localhost:3000',
    'http://localhost:4321',
    'http://127.0.0.1:3000',
    'http://127.0.0.1:4321',
]);

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'up'],

    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],

    'allowed_origins' => ! empty($configuredOrigins) ? $configuredOrigins : $defaultOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Accept', 'Origin'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,

];
