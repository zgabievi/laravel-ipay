<?php

return [

    /**
     * This value decides to log or not to log requests.
     */
    'debug' => env('IPAY_DEBUG', false),

    /**
     * Payment url from Bank of Georgia
     */
    'url' => env('IPAY_URL', 'https://ipay.ge/opay/api/v1'),

    /**
     * Callback url where will be redirected after a success/failure payment
     */
    'redirect_url' => env('IPAY_REDIRECT_URL', '/payments/redirect'),

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
