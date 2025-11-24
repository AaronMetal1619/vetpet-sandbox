<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\FirebaseAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\CitaController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

//
// 📌 Rutas públicas
//

// Registro y login
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Productos visibles sin login
Route::get('/productos', [ProductoController::class, 'index']);

// Crear cita (desde chatbot o cliente)
Route::post('/citas', [CitaController::class, 'store']);

//
// 🔒 Rutas protegidas por Sanctum
//
Route::middleware('auth:sanctum')->group(function () {

    // Sesión y perfil
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Actualizar perfil
    Route::post('/update-profile/{id}', [PerfilController::class, 'update']);

    // CRUD productos para usuarios autenticados
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::put('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);

    //
    // 👑 Rutas solo para administradores
    //
    Route::middleware('role:admin')->group(function () {
        Route::post('/admin/create-user', [AuthController::class, 'createUser']);
    });
});

//
// 🌐 Socialite Login (Facebook / Google)
//
Route::get('/auth/{provider}/redirect', function ($provider) {
    return Socialite::driver($provider)->stateless()->redirect();
});

Route::get('/auth/{provider}/callback', function ($provider) {
    $socialUser = Socialite::driver($provider)->stateless()->user();

    $user = User::updateOrCreate(
        ['email' => $socialUser->getEmail()],
        [
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'password' => bcrypt(str()->random(16)),
        ]
    );

    $token = $user->createToken('authToken')->plainTextToken;

    return redirect("https://vetpetfront.onrender.com/social-login-success?token=$token");
});

//
// 🔐 Firebase login
//
Route::post('/auth/firebase', [FirebaseAuthController::class, 'handle']);

//rutas suscripcion falsa
Route::post('/fake-subscribe', function (Request $request) {
    $user = auth()->user();

    $user->subscription_type = $request->plan;
    $user->subscription_active = true;
    $user->save();

    return response()->json([
        "message" => "Suscripción activada (modo prueba)",
        "plan" => $request->plan
    ]);
});



//
// 🌐 Rutas personalizadas de SocialiteController
//
Route::get('/auth/facebook/redirect', [SocialiteController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialiteController::class, 'handleFacebookCallback']);
Route::get('/auth/google/redirect', [SocialiteController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);
