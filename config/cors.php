<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'webhook/*',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:5678',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5678',
        '*', // si NO quieres permitir todo, puedo quitarte esta línea
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
