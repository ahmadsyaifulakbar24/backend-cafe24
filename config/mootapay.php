<?php

return [
    'url' => 'https://api.mootapay.com/api/v1/',
    'debug_url' => 'https://private-4f69da-mootapay.apiary-mock.com/api/v1/',
    'merchant_id' => env("MOOTAPAY_MERCHANT_ID"),
    'token' => env('MOOTAPAY_TOKEN'),
    'max_unique_code' => env("MAX_UNIQUE_CODE"),
    'debug' => env("MOOTAPAY_DEBUG", false),
    'callback_url' => '/api/transaction/handle_mootapay',
];