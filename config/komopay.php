<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Komopay API
    |--------------------------------------------------------------------------
    |
    | base_url   Root URL of the Komopay API used by the HTTP implementations.
    | use_mock   When true, the ApiServiceProvider binds every Api contract
    |            to its Mock implementation (JSON fixtures in database/mocks).
    |            When false, contracts are bound to HTTP implementations
    |            backed by KomopayClient.
    | api_key    Bearer token used by KomopayClient when calling the real API.
    | timeout    Request timeout in seconds.
    */

    'base_url' => env('KOMOPAY_API_BASE_URL', 'http://localhost:8080'),

    'use_mock' => (bool) env('KOMOPAY_USE_MOCK_API', true),

    'api_key' => env('KOMOPAY_API_KEY'),

    'timeout' => (int) env('KOMOPAY_API_TIMEOUT', 15),

    'mocks_path' => env('KOMOPAY_MOCKS_PATH', database_path('mocks')),
];
