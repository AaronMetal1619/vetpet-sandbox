<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
{
    Schema::create('pets', function (Blueprint $table) {
        $table->id();
        // Relación con el usuario (dueño de la cuenta)
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        // Datos básicos
        $table->string('name');
        $table->string('owner_name'); // Nombre del dueño (si es diferente al usuario)
        $table->integer('age');
        $table->string('breed'); // Raza

        // Datos médicos (Cardex) - Nullable por si no tiene nada
        $table->text('allergies')->nullable(); 
        $table->text('chronic_diseases')->nullable();
        $table->text('surgeries')->nullable();

        // Foto (guardaremos la ruta del archivo)
        $table->string('photo_url')->nullable();

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pets');
    }
}
