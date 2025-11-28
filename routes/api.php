<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController; // Importante
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\FirebaseAuthController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/productos', [ProductoController::class, 'index']);
Route::post('/citas', [CitaController::class, 'store']);

// Protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // GESTIÓN DE VETERINARIAS (Rutas que daban 404)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/admin/users', [UserController::class, 'store']); // Usamos store del UserController
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Perfil y Productos
    Route::post('/update-profile/{id}', [PerfilController::class, 'update']);
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::put('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);
});

// Socialite y Firebase (Sin cambios)
Route::get('/auth/{provider}/redirect', function ($provider) {
    return Socialite::driver($provider)->stateless()->redirect();
});
Route::get('/auth/{provider}/callback', function ($provider) {
    try {
        $socialUser = Socialite::driver($provider)->stateless()->user();
        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            ['name' => $socialUser->getName(), 'email' => $socialUser->getEmail(), 'password' => bcrypt(str()->random(16)), 'role' => 'user']
        );
        $token = $user->createToken('authToken')->plainTextToken;
        return redirect("https://vetpetfront.onrender.com/social-login-success?token=$token");
    } catch (\Exception $e) {
        return redirect("https://vetpetfront.onrender.com/login?error=social_login_failed");
    }
});
Route::post('/auth/firebase', [FirebaseAuthController::class, 'handle']);