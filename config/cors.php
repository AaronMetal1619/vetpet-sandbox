<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'webhook/*',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // 👇 ESTA ES LA CLAVE: Tu dominio real de producción
        'https://vetpetfront.onrender.com',
        
        // Entornos locales (Vite suele usar 5173, React puro 3000)
        'http://localhost:5173',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // ✅ Ahora sí funciona porque los orígenes son explícitos
];