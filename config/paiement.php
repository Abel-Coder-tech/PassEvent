<?php

return [

    'methodes' => [

        'kkiapay' => [
            'nom' => 'KKiaPay',
            'description' => 'Paiement par Mobile Money (MTN, Moov, Celtiis)',
            'icone' => 'bi-phone',
            'couleur' => '#542680',
            'active' => env('KKIAPAY_API_KEY') ? true : false,
            'sandbox' => env('KKIAPAY_SANDBOX', true),
            'cle' => env('KKIAPAY_API_KEY'),
        ],

        'sebpay' => [
            'nom' => 'Sebpay',
            'description' => 'Paiement Mobile Money (MTN, Moov, Orange, Wave)',
            'icone' => 'bi-shield-check',
            'couleur' => '#198754',
            'active' => env('SEBPAY_PUBLIC_KEY') && env('SEBPAY_SECRET_KEY'),
            'sandbox' => env('SEBPAY_SANDBOX', true),
            'public_key' => env('SEBPAY_PUBLIC_KEY'),
            'secret_key' => env('SEBPAY_SECRET_KEY'),
            'api_url' => env('SEBPAY_API_URL', 'https://newapi.sebpay.bj/api/v1'),
            'sandbox_url' => env('SEBPAY_SANDBOX_URL', 'https://sandbox-api.sebpay.bj/api/v1'),
            'operateurs' => [
                'mtn' => 'MTN',
                'moov' => 'Moov',
                'orange' => 'Orange',
                'wav' => 'Wave',
            ],
        ],

    ],

    'defaut' => 'kkiapay',

];
