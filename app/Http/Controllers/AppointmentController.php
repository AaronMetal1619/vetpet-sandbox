<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'date' => 'required|date',
            'time' => 'required', // Puedes validar formato H:i
            'reason' => 'required|string',
        ]);

        // Seguridad: Verificar que la mascota pertenezca al usuario logueado
        $pet = Pet::where('id', $request->pet_id)->where('user_id', Auth::id())->first();
        if (!$pet) {
            return response()->json(['message' => 'Mascota no autorizada'], 403);
        }

        $cita = Appointment::create([
            'pet_id' => $request->pet_id,
            'date' => $request->date,
            'time' => $request->time,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        return response()->json($cita, 201);
    }
}