<?php

namespace App\Http\Controllers;
use App\Models\Cita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'nombre' => 'required|string|max:255',
        'fecha' => 'required|string|max:255',
        'motivo' => 'required|string|max:255',
    ]);

    $cita = Cita::create($validated);

    return response()->json([
        'message' => 'Cita registrada correctamente',
        'cita' => $cita
    ], 201);
}
}
