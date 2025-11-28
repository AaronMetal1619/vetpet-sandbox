<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Artisan; // <--- IMPORTANTE: Agregamos esto

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 🔵 Socialite Facebook
Route::get('/auth/facebook/redirect', [SocialiteController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialiteController::class, 'handleFacebookCallback']);

// 💬 Vista básica para probar el chatbot
Route::get('/chat', function () {
    return view('chat');
});

// 🔄 Endpoint del chatbot (si usas vistas Laravel)
Route::post('/chatbot', [ChatbotController::class, 'handle']);

// 🏠 Vista principal del backend
Route::get('/', function () {
    return view('welcome');
});

//
// 🚨 RUTA DE EMERGENCIA PARA LIMPIAR CACHÉ (EL ARREGLO)
//
Route::get('/fix-laravel', function () {
    try {
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        
        return "<h1>✅ ¡SOLUCIONADO!</h1><p>La memoria caché de Laravel ha sido borrada. Tus nuevas rutas de API ya deberían funcionar.</p>";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});