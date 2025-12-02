<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
    Schema::create('appointments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pet_id')->constrained()->onDelete('cascade'); // Relación con mascota
        $table->date('date');      // Fecha: 2025-10-20
        $table->time('time');      // Hora: 14:00
        $table->string('reason');  // Motivo: Vacuna
        $table->string('status')->default('pending'); // pending, completed, cancelled
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
        Schema::dropIfExists('appointments');
    }
}
