<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Elasticsearch connection settings (basic)
    |--------------------------------------------------------------------------
    */

    'default' => [
        'hosts' => [
            [
                'host' => env('ES_HOST'),
                'port' => env('ES_PORT'),
            ],
        ],
        'username' => env('ES_USER'),
        'password' => env('ES_PASSWORD'),
        'skip_tls_verify' => filter_var(env('ES_SKIP_TLS_VERIFY', false), FILTER_VALIDATE_BOOLEAN),
    ], // <-- Hatanın kaynağı olan EKSİK VİRGÜL buraya eklendi!

    'indices' => [
        'settings' => [
            'default' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ],
        ],
        'mappings' => [
            'default' => [],
        ],
    ],
];