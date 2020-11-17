<?php

return [

    /**
     * This value decides to log or not to log requests.
     */
    'debug' => env('IPAY_DEBUG', false),

    /**
     * Payment url from Bank of Georgia
     */
    'url' => env('IPAY_URL', 'https://dev.ipay.ge/opay/api/v1'),

    /**
     * Callback url where will be redirected after a success/failure payment
     */
    'payment_callback_url' => env('IPAY_PAYMENT_CALLBACK_URL', url('/payments/callback')),

    /**
     * Callback url where will be redirected after the refund process
     */
    'refund_callback_url' => env('IPAY_REFUND_CALLBACK_URL', url('/refunds/callback')),

    /**
     * Client ID provided by Bank of Georgia
     */
    'client_id' => env('IPAY_CLIENT_ID'),

    /**
     * Default language for Bank of Georgia payment
     */
    'language' => env('IPAY_LANGUAGE', 'ka'),

    /**
     * Secret key provided by Bank of Georgia
     */
    'secret_key' => env('IPAY_SECRET_KEY'),
];
