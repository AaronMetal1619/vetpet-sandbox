<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Auth\SocialiteController;

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
