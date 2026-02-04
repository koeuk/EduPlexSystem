<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bakong API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Bakong KHQR payment integration
    |
    */

    // API Base URL
    'base_url' => env('BAKONG_BASE_URL', 'https://api-bakong.nbc.gov.kh'),

    // Merchant credentials
    'merchant_id' => env('BAKONG_MERCHANT_ID'),
    'merchant_name' => env('BAKONG_MERCHANT_NAME', 'EduPlex'),
    'api_token' => env('BAKONG_API_TOKEN'),

    // Account information
    'account_id' => env('BAKONG_ACCOUNT_ID'), // Bakong account ID
    'acquiring_bank' => env('BAKONG_ACQUIRING_BANK', 'EduPlex'), // Bank name

    // Currency
    'currency' => env('BAKONG_CURRENCY', 'USD'), // USD or KHR

    // Callback/Webhook URL
    'webhook_url' => env('BAKONG_WEBHOOK_URL'),
    'webhook_secret' => env('BAKONG_WEBHOOK_SECRET'),

    // Settings
    'timeout' => env('BAKONG_TIMEOUT', 30),
    'verify_ssl' => env('BAKONG_VERIFY_SSL', true),

    // Test mode
    'test_mode' => env('BAKONG_TEST_MODE', true),
];
