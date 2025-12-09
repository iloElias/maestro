<?php

return [
    'default' => 'redis',

    'connections' => [
        'redis' => [
            'driver' => 'redis',
        ],

        'rabbitmq' => [
            'driver' => 'rabbitmq',

            'host' => env('RABBITMQ_HOST', 'localhost'),
            'port' => env('RABBITMQ_PORT', 5672),
            'username' => env('RABBITMQ_USERNAME', 'guest'),
            'password' => env('RABBITMQ_PASSWORD', 'guest'),
            'vhost' => env('RABBITMQ_VHOST', '/'),
        ],
    ],
];
