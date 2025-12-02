<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    // Estos son los campos que permitimos llenar desde el formulario
    protected $fillable = [
        'user_id',
        'name',
        'owner_name',
        'age',
        'breed',
        'allergies',
        'chronic_diseases',
        'surgeries',
        'photo_url',
    ];

    // Relación: Una mascota pertenece a un Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}