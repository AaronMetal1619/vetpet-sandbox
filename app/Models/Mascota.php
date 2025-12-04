<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
    use HasFactory;

    // Nombre explícito de la tabla
    protected $table = 'mascotas';

    // Campos que pueden ser asignados masivamente (Mass Assignment)
    protected $fillable = [
        'user_id',
        'nombre',
        'especie',
        'raza',
        'genero',
        'edad',
        'color',
        'peso',
        'alergias',
        'detalle_alergias',
        'historial_medico',
        'veterinario_encargado',
        'foto',
    ];

    // Casting: Convierte automáticamente 'alergias' a true/false al leerlo de la BD
    protected $casts = [
        'alergias' => 'boolean',
    ];

    // Ocultar user_id para que no aparezca en las respuestas JSON de la API
    protected $hidden = [
        'user_id',
    ];

    /**
     * Relación: Una mascota pertenece a un Usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
