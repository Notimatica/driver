<?php

return [
    'providers' => [
        \Notimatica\Driver\Providers\Chrome::NAME => [
            'api_key' => '',
            'sender_id' => '',
            'ttl' => 86400,
            'service_url' => 'https://android.googleapis.com/gcm/send',
            'batch_chunk_size' => 1000,
            'concurrent_requests' => 10,
        ],
        \Notimatica\Driver\Providers\Firefox::NAME => [
            'ttl' => 86400,
            'service_url' => 'https://updates.push.services.mozilla.com/push/v1',
            'concurrent_requests' => 100,
        ],
        \Notimatica\Driver\Providers\Safari::NAME => [
            'safari_web_id' => '',
            'subscribe_url' => '/subscribe/safari',
            'service_url' => 'ssl://gateway.push.apple.com:2195',
            'assets' => [
                'root' => '/safari_push_data',
                'package' => 'safari-package.zip',
                'certificates' => [
                    'p12'       => 'certificate.p12',
                    'pem'       => 'certificate.pem',
                    'password'  => 'certificate.password',
                ],
                'icons' => [
                    'icon_16'   => 'icon_16x16.png',
                    'icon_32'   => 'icon_32x32.png',
                    'icon_64'   => 'icon_32x32@2x.png',
                    'icon_128'  => 'icon_128x128.png',
                    'icon_256'  => 'icon_128x128@2x.png',
                ]
            ],
        ],
    ],
    'payload' => [
        'subscriber_lifetime' => 86400,
        'url' => ''
    ]
];
