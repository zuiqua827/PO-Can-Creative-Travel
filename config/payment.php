<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway Driver
    |--------------------------------------------------------------------------
    |
    | Supported drivers: "fake" (simulation mode), "midtrans"
    | In production, set PAYMENT_DRIVER=midtrans in your .env.
    |
    */

    'driver' => env('PAYMENT_DRIVER', 'fake'),

    /*
    |--------------------------------------------------------------------------
    | Default Payment Currency & Expiry
    |--------------------------------------------------------------------------
    |
    | CAN Travel prices all trips in Indonesian Rupiah (IDR).
    | Default order payment expiration window is 120 minutes (2 hours).
    |
    */

    'currency' => env('PAYMENT_CURRENCY', 'IDR'),

    'expiry_minutes' => (int) env('PAYMENT_EXPIRY_MINUTES', 120),

    'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET', 'test_secret_can_travel'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Drivers Configuration
    |--------------------------------------------------------------------------
    */

    'drivers' => [

        'fake' => [
            'name' => 'simulation',
            'auto_confirm' => env('PAYMENT_SIMULATION_AUTO_CONFIRM', false),
        ],

        'midtrans' => [
            'server_key' => env('MIDTRANS_SERVER_KEY', ''),
            'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
            'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
            'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
            'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
                ? 'https://app.midtrans.com/snap/v1/transactions'
                : 'https://app.sandbox.midtrans.com/snap/v1/transactions',
            'api_base_url' => env('MIDTRANS_IS_PRODUCTION', false)
                ? 'https://api.midtrans.com'
                : 'https://api.sandbox.midtrans.com',
        ],

    ],

];
