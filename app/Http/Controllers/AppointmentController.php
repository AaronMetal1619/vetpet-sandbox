<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    // Listar citas para el Dashboard (GET /api/appointments)
    public function index()
    {
        // Traemos las citas con la Mascota y el Dueño de la mascota (Relaciones anidadas)
        // Ordenamos por fecha y hora para ver las próximas primero
        $citas = Appointment::with(['pet.user', 'pet.medicalHistory']) // 'pet' y dentro de pet, el 'user'
                    ->where('status', '!=', 'completed') // Opcional: No mostrar las finalizadas
                    ->orderBy('date', 'asc')
                    ->orderBy('time', 'asc')
                    ->get();

        return response()->json($citas);
    }
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
    // Finalizar Cita (POST /api/appointments/{id}/complete)
    public function complete(Request $request, $id)
    {
        $request->validate([
            'diagnosis' => 'required|string', // Diagnóstico
            'treatment' => 'required|string', // Tratamiento / Receta
        ]);

        // 1. Buscar la cita
        $appointment = Appointment::findOrFail($id);

        // 2. Crear el Registro Médico (Historial)
        // Usamos el modelo MedicalRecord que ya creamos antes
        \App\Models\MedicalRecord::create([
            'pet_id' => $appointment->pet_id,
            'clinic_name' => 'Mi Veterinaria', // Aquí podrías poner el nombre real del vet logueado
            'visit_date' => now()->toDateString(),
            'diagnosis' => $request->diagnosis,
            'treatment' => $request->treatment,
        ]);

        // 3. Actualizar estado de la cita
        $appointment->status = 'completed';
        $appointment->save();

        return response()->json(['message' => 'Cita finalizada correctamente']);
    }
}