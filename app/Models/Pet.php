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
    // RELACIÓN 1: Historial Médico (Una mascota tiene muchos registros)
    // El nombre de la función 'medicalHistory' DEBE coincidir con lo que pusiste en el controlador
    public function medicalHistory()
    {
        return $this->hasMany(MedicalRecord::class)->orderBy('visit_date', 'desc');
    }

    // RELACIÓN 2: Próxima Cita (Una mascota tiene una cita futura)
    // Esto busca la cita más cercana que sea mayor o igual a "hoy"
    public function nextAppointment()
    {
        // Asumiendo que tu modelo de citas se llama 'Appointment' y el campo de fecha es 'date'
        return $this->hasOne(Cita::class)
                    ->where('date', '>=', now()) // Solo fechas futuras
                    ->orderBy('date', 'asc');    // La más cercana primero
    }

    // Relación: Una mascota pertenece a un Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}