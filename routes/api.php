<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\CitaController;
// Registro y login
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rutas públicas de productos (si quieres que se puedan ver sin login)
Route::get('/productos', [ProductoController::class, 'index']);

// Crear cita desde el chatbot o cliente
Route::post('/citas', [CitaController::class, 'store']);


// =========================================================
// 🔒 Rutas protegidas con autenticación
// =========================================================
Route::middleware('auth:sanctum')->group(function () {

    // Cerrar sesión y ver perfil actual
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Actualizar perfil del usuario autenticado
    Route::post('/update-profile/{id}', [PerfilController::class, 'update']);

    // CRUD de productos (solo para usuarios autenticados)
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::put('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);

    // =====================================================
    // 🛠️ Rutas solo para administradores
    // =====================================================
    Route::middleware('role:admin')->group(function () {
        Route::post('/admin/create-user', [AuthController::class, 'createUser']); // Crear usuarios desde el panel
        // Puedes agregar aquí más rutas exclusivas del panel admin
    });
});