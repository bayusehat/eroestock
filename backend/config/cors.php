<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The application now serves Blade + Livewire views directly via session-based
    | auth, so CORS is only needed for the API routes (/api/*) to support potential
    | mobile apps or third-party integrations. The FRONTEND_URL env var is no longer
    | required for the main web app, but kept for backward-compatible API consumers.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
