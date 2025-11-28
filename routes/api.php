<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

// Controladores
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FirebaseAuthController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\UserController; // <--- IMPORTANTE

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 📌 Rutas públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/productos', [ProductoController::class, 'index']);
Route::post('/citas', [CitaController::class, 'store']);

// 🔒 Rutas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Sesión
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/update-profile/{id}', [PerfilController::class, 'update']);

    // Productos
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::put('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);

    // 👥 GESTIÓN DE USUARIOS (VETERINARIAS)
    // Estas son las rutas que te estaban dando 404
    Route::get('/users', [UserController::class, 'index']);       // Listar
    Route::post('/admin/users', [UserController::class, 'store']); // Crear (Nuevo)
    Route::put('/users/{id}', [UserController::class, 'update']); // Editar
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // Eliminar

    // Suscripción falsa
    Route::post('/fake-subscribe', function (Request $request) {
        $user = auth()->user();
        $user->subscription_type = $request->plan;
        $user->subscription_active = true;
        $user->save();
        return response()->json(["message" => "Suscripción activada", "plan" => $request->plan]);
    });
});

// 🌐 Socialite y Firebase (Sin cambios)
Route::get('/auth/{provider}/redirect', function ($provider) {
    return Socialite::driver($provider)->stateless()->redirect();
});
Route::get('/auth/{provider}/callback', function ($provider) {
    try {
        $socialUser = Socialite::driver($provider)->stateless()->user();
        $user = \App\Models\User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(str()->random(16)),
                'role' => 'user'
            ]
        );
        $token = $user->createToken('authToken')->plainTextToken;
        return redirect("https://vetpetfront.onrender.com/social-login-success?token=$token");
    } catch (\Exception $e) {
        return redirect("https://vetpetfront.onrender.com/login?error=social_login_failed");
    }
});
Route::post('/auth/firebase', [FirebaseAuthController::class, 'handle']);