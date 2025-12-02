<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    // FALTABA ESTO: Decirle a Laravel qué campos vamos a usar
    protected $fillable = [
        'pet_id',
        'clinic_name',
        'visit_date',
        'diagnosis',
        'treatment'
    ];

    // Relación inversa (opcional pero recomendada)
    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
