<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Elasticsearch connection settings (basic)
    |--------------------------------------------------------------------------
    */

    // Backwards-compatible keys for packages that expect a simple `elasticsearch.host` value
    // Build a single host string from `ES_HOST` and `ES_PORT` so Matchish driver can consume it.
    'host' => (function () {
        $host = env('ES_HOST');
        $port = env('ES_PORT');
        if (empty($host)) {
            return '';
        }
        return $host . ($port ? ':' . $port : '');
    })(),

    'default' => [
        'hosts' => [
            [
                'host' => env('ES_HOST'),
                'port' => env('ES_PORT'),
            ],
        ],
        'username' => env('ES_USER'),
        'password' => env('ES_PASSWORD'),
        'skip_tls_verify' => filter_var(env('ES_SKIP_TLS_VERIFY', true), FILTER_VALIDATE_BOOLEAN),
    ],

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