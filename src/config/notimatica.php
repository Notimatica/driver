<?php

return [
    'providers' => [
        \Notimatica\Driver\Providers\Chrome::NAME => [
            'api_key' => '',
            'sender_id' => '',
            'ttl' => 86400,
            'url' => 'https://android.googleapis.com/gcm/send',
            'batch_chunk_size' => 1000,
            'concurrent_requests' => 10,
        ],
        \Notimatica\Driver\Providers\Firefox::NAME => [
            'ttl' => 86400,
            'url' => 'https://updates.push.services.mozilla.com/push/v1',
            'concurrent_requests' => 100,
        ],
        \Notimatica\Driver\Providers\Safari::NAME => [
            'storage_root' => '/safari_push_data',
            'subscribe_url' => '/subscribe/safari',
            'safari_web_id' => '',
            'package_path' => null,
            'url' => 'ssl://gateway.push.apple.com:2195',
        ],
    ],
    'payload' => [
        'subscriber_lifetime' => 86400,
        'url' => ''
    ]
];
