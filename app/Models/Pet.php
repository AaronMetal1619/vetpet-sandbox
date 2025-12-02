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
    
    // app/Models/Pet.php
    public function nextAppointment(){
             // Busca la cita en la tabla 'appointments'
        return $this->hasOne(Appointment::class)
                 ->where('date', '>=', now()) // Solo fechas futuras
                 ->orderBy('date', 'asc');    // La más cercana
        }
    // RELACIÓN 1: Historial Médico (Una mascota tiene muchos registros)
    // El nombre de la función 'medicalHistory' DEBE coincidir con lo que pusiste en el controlador
    public function medicalHistory()
    {
        return $this->hasMany(MedicalRecord::class)->orderBy('visit_date', 'desc');
    }

    // Relación: Una mascota pertenece a un Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}