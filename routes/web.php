<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Auth\SocialiteController;

/*
|--------------------------------------------------------------------------
| Web Routes - HERRAMIENTA DE DIAGNÓSTICO
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Ruta Socialite y Chatbot (mantenemos lo que tenías)
Route::get('/auth/facebook/redirect', [SocialiteController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialiteController::class, 'handleFacebookCallback']);
Route::get('/chat', function () { return view('chat'); });
Route::post('/chatbot', [ChatbotController::class, 'handle']);

// 🔥 LA RUTA MAESTRA DE DIAGNÓSTICO 🔥
Route::get('/debug-routes', function () {
    try {
        // 1. Forzar limpieza agresiva
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('optimize:clear');

        // 2. Obtener la lista de rutas que Laravel "ve" ahora mismo
        $routes = Route::getRoutes();
        $lista = [];
        
        foreach ($routes as $route) {
            // Solo nos interesan las rutas de API
            if (str_contains($route->uri(), 'api')) {
                $lista[] = [
                    'method' => implode('|', $route->methods()),
                    'uri'    => $route->uri(),
                    'action' => $route->getActionName()
                ];
            }
        }

        return response()->json([
            'status' => 'Caché borrada exitosamente',
            'total_rutas_api' => count($lista),
            'rutas_detectadas' => $lista
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
Route::get('/run-storage-link', function () {
    // Esto ejecuta el comando php artisan storage:link internamente
    Artisan::call('storage:link');
    return '¡Comando storage:link ejecutado correctamente! Ya deberías ver las imágenes.';
});
Route::get('/run-migrate', function () {
    Illuminate\Support\Facades\Artisan::call('migrate --force');
    return 'Migración ejecutada con éxito';
});
Route::get('/clear-cache', function () {
    try {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:cache'); // Reconstruye la caché con los datos nuevos
        return "<h1>¡Caché limpiada y configuración reconstruida!</h1>";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});