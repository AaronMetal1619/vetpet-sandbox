<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Artisan; // <--- Necesario para ejecutar comandos

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirección de la raíz
Route::get('/', function () {
    return view('welcome');
});

// Socialite
Route::get('/auth/facebook/redirect', [SocialiteController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialiteController::class, 'handleFacebookCallback']);

// Chatbot (si usas vistas)
Route::get('/chat', function () {
    return view('chat');
});
Route::post('/chatbot', [ChatbotController::class, 'handle']);

// 🛠️ RUTA DE REPARACIÓN (ESTO SOLUCIONA EL ERROR 404)
Route::get('/fix-laravel', function () {
    try {
        // 1. Borrar caché de rutas (El culpable principal)
        Artisan::call('route:clear');
        
        // 2. Borrar caché de configuración
        Artisan::call('config:clear');
        
        // 3. Borrar caché de aplicación
        Artisan::call('cache:clear');
        
        // 4. Re-optimizar (Opcional)
        // Artisan::call('optimize'); 
        
        return "<h1 style='color:green'>✅ ÉXITO: Sistema reiniciado</h1>
                <p>Las rutas se han limpiado. Render ahora reconoce '/api/users'.</p>
                <p>Vuelve a tu Frontend y recarga la página.</p>";
    } catch (\Exception $e) {
        return "<h1 style='color:red'>❌ ERROR</h1> <p>" . $e->getMessage() . "</p>";
    }
});