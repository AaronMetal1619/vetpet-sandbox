<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

// Controladores
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\FirebaseAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

//
// 🔓 RUTAS PÚBLICAS (No requieren Token)
//
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Productos y Citas (Crear) visibles para todos
Route::get('/productos', [ProductoController::class, 'index']);
Route::post('/citas', [CitaController::class, 'store']); 

// 🔥 CRUCIAL: Lista de usuarios pública para que n8n pueda leer las veterinarias
Route::get('/users', [UserController::class, 'index']); 


//
// 🔒 RUTAS PROTEGIDAS (Requieren Token - Admin/Partner/User logueado)
//
Route::middleware('auth:sanctum')->group(function () {

    // --- SESIÓN Y PERFIL ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/update-profile/{id}', [PerfilController::class, 'update']);

    // --- GESTIÓN DE USUARIOS (ADMINISTRACIÓN) ---
    // Nota: GET /users ahora es pública (arriba), pero crear/editar/borrar sigue protegido
    Route::post('/admin/users', [UserController::class, 'store']); // Crear Veterinaria
    Route::put('/users/{id}', [UserController::class, 'update']);  // Editar
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // Eliminar

    // --- GESTIÓN DE CITAS (DASHBOARD) ---
    Route::get('/citas', [CitaController::class, 'index']);       // Ver lista de citas
    Route::delete('/citas/{id}', [CitaController::class, 'destroy']); // Borrar cita

    // --- GESTIÓN DE PRODUCTOS ---
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::put('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);
    
    // --- SUSCRIPCIÓN FALSA (TESTING) ---
    Route::post('/fake-subscribe', function (Request $request) {
        $user = auth()->user();
        $user->subscription_type = $request->plan;
        $user->subscription_active = true;
        $user->save();
        return response()->json(["message" => "Suscripción activada", "plan" => $request->plan]);
    });
});

//
// 🌐 SOCIAL LOGIN (Facebook / Google)
//
Route::get('/auth/{provider}/redirect', function ($provider) {
    return Socialite::driver($provider)->stateless()->redirect();
});

Route::get('/auth/{provider}/callback', function ($provider) {
    try {
        $socialUser = Socialite::driver($provider)->stateless()->user();
        $user = User::updateOrCreate(
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

//
// 🔐 FIREBASE LOGIN
//
Route::post('/auth/firebase', [FirebaseAuthController::class, 'handle']);