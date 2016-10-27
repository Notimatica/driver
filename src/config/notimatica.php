<?php

return [
    'providers' => [
        \Notimatica\Driver\Providers\Chrome::NAME => [
            'ttl' => 86400,
            'batch_chunk_size' => 1000,
            'url' => 'https://android.googleapis.com/gcm/send',
            'concurrent_requests' => 10,
        ],
        \Notimatica\Driver\Providers\Firefox::NAME => [
            'ttl' => 86400,
            'url' => 'https://updates.push.services.mozilla.com/push/v1',
            'concurrent_requests' => 100,
        ],
        \Notimatica\Driver\Providers\Safari::NAME => [
            'url' => 'ssl://gateway.push.apple.com:2195',
            'package_path' => '/',
        ],
    ],
    'payload' => [
        'storage' => 'cache',
        'payload_lifetime' => 86400,
        'subscriber_lifetime' => 86400,
    ],
    'statistics' => [
        'storage' => 'model',
    ],
];
