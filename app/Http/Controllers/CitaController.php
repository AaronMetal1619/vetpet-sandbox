<?php

namespace App\Http\Controllers;
use App\Models\Cita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    // Listar citas (GET)
    public function index()
    {
        // Ordenarlas por fecha (puedes mejorar esto luego)
        return response()->json(Cita::orderBy('created_at', 'desc')->get());
    }

    // Crear cita (POST)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255', // Nombre del cliente/mascota
            'fecha' => 'required|string',          // Guardaremos fecha y hora combinadas o separadas
            'motivo' => 'required|string|max:255',
        ]);

        $cita = Cita::create($validated);

        return response()->json([
            'message' => 'Cita registrada correctamente',
            'cita' => $cita
        ], 201);
    }

    // Eliminar cita (DELETE)
    public function destroy($id)
    {
        $cita = Cita::find($id);
        if(!$cita) return response()->json(['message' => 'No encontrada'], 404);
        $cita->delete();
        return response()->json(['message' => 'Cita eliminada']);
    }
}