<?php

return [
    'paths' => ['api/*','sanctum/csrf-cookie'], // Habilita para las rutas API

    'allowed_origins' => ['https://vetpetfront.onrender.com'],  // Permite todos los métodos (GET, POST, PUT, DELETE, etc.)

    'allowed_methods' => ['*'], //los métodos permitidos (cambio por  Aaron)
    'allowed_origins' => ['*'], // Permite todos los orígenes (si es solo para desarrollo)

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Permite todos los encabezados

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // Permite el uso de credenciales (cookies, autenticación HTTP básica, etc.), pero lo quitare
];
