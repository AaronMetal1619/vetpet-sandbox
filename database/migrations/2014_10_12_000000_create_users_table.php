<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Datos básicos
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Roles y Tipos
            $table->string('role')->default('user'); // admin, partner, user
            $table->string('partner_type')->nullable(); // veterinaria, estetica, etc.
            
            // Datos de Contacto (Agregados para Veterinarias)
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            
            // 📍 GEOLOCALIZACIÓN (Nuevo para el Mapa)
            // Usamos decimal con alta precisión (10 dígitos en total, 8 decimales)
            // Ejemplo: 19.43260770
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Otros
            $table->string('photo')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};