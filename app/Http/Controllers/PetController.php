<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
    // 1. OBTENER MASCOTAS (GET)
    public function index()
    {
        // Devuelve solo las mascotas del usuario logueado
        return Pet::where('user_id', Auth::id())->get();
    }

    // 2. GUARDAR NUEVA MASCOTA (POST)
    public function store(Request $request)
    {
        // Validamos que los datos vengan bien
        $request->validate([
            'name' => 'required|string',
            'owner_name' => 'required|string',
            'age' => 'required|integer',
            'breed' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validación de imagen
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id(); // Asignamos la mascota al usuario actual

        // Lógica para guardar la imagen
        if ($request->hasFile('photo')) {
            // Guarda en la carpeta 'public/pets' dentro del storage
            $path = $request->file('photo')->store('pets', 'public');
            // Crea la URL completa para que React la pueda leer
            $data['photo_url'] = url('storage/' . $path);
        }

        $pet = Pet::create($data);

        return response()->json($pet, 201);
    }

    // 3. ACTUALIZAR MASCOTA (PUT/POST)
    public function update(Request $request, $id)
    {
        // Buscamos la mascota y verificamos que sea del usuario
        $pet = Pet::where('user_id', Auth::id())->findOrFail($id);

        $data = $request->all();

        // Lógica para reemplazar la imagen
        if ($request->hasFile('photo')) {
            // Borramos la imagen vieja si existe (opcional pero recomendado para no llenar el disco)
            // ... código de borrado ...

            $path = $request->file('photo')->store('pets', 'public');
            $data['photo_url'] = url('storage/' . $path);
        }

        $pet->update($data);

        return response()->json($pet, 200);
    }

    // 4. ELIMINAR MASCOTA (DELETE)
    public function destroy($id)
    {
        $pet = Pet::where('user_id', Auth::id())->findOrFail($id);
        $pet->delete();
        return response()->json(['message' => 'Mascota eliminada'], 200);
    }
}