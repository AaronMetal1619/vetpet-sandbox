<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMascotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Verificamos si la tabla existe para no romper migraciones anteriores
        if (!Schema::hasTable('mascotas')) {
            Schema::create('mascotas', function (Blueprint $table) {
                $table->id();

                // Relación con usuario (CRÍTICO)
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                // Datos básicos
                $table->string('nombre');
                $table->string('especie');
                $table->string('raza');
                $table->string('genero'); // Macho/Hembra
                $table->integer('edad')->nullable();
                $table->string('color')->nullable();
                $table->string('peso')->nullable();

                // Datos médicos
                $table->boolean('alergias')->default(false);
                $table->text('detalle_alergias')->nullable();
                $table->text('historial_medico')->nullable();
                $table->string('veterinario_encargado')->nullable();

                // Foto (IMPORTANTE para tu feature)
                $table->string('foto')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
}
