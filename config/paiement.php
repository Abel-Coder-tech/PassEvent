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
            'description' => 'Paiement sécurisé par Sebpay',
            'icone' => 'bi-shield-check',
            'couleur' => '#198754',
            'active' => env('SEBPAY_API_KEY') ? true : false,
            'sandbox' => env('SEBPAY_SANDBOX', true),
            'api_key' => env('SEBPAY_API_KEY'),
            'public_key' => env('SEBPAY_PUBLIC_KEY'),
            'secret_key' => env('SEBPAY_SECRET_KEY'),
        ],

    ],

    'defaut' => 'kkiapay',

];
