<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicalRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
    Schema::create('medical_records', function (Blueprint $table) {
        $table->id();
        // Relación: ¿De qué mascota es este historial?
        $table->foreignId('pet_id')->constrained()->onDelete('cascade');
        
        // Datos que te pidieron
        $table->string('clinic_name'); // Ej: "Veterinaria Heal"
        $table->date('visit_date');    // Ej: 2025-11-20
        $table->text('diagnosis');     // Ej: "Tos de perrera"
        $table->text('treatment')->nullable(); // Ej: "Jarabe x 5 días" (Opcional)
        
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
        Schema::dropIfExists('medical_records');
    }
}
