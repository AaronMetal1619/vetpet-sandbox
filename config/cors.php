<?php

return [
    'paths' => ['api/*',"sanctum/csrf-cookie"], // Habilita para las rutas API
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],  // Permite todos los métodos (GET, POST, PUT, DELETE, etc.)


    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Permite todos los encabezados

    'exposed_headers' => [],
    
    'max_age' => 0,

    'supports_credentials' => false,
];
