<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * 📋 Listar usuarios (con filtro)
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filtro inteligente para veterinarias
        if ($request->has('role')) {
            if ($request->role === 'veterinaria') {
                $query->where('role', 'partner')
                      ->where('partner_type', 'veterinaria');
            } else {
                $query->where('role', $request->role);
            }
        }

        return response()->json($query->get());
    }

    /**
     * ➕ Crear usuario (Veterinaria)
     */
    public function store(Request $request)
    {
        // Validar datos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|string',
            'partner_type' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        // Crear usuario
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'partner_type' => $validated['partner_type'] ?? null,
            // Si no tienes estas columnas en BD, comenta las siguientes 2 líneas:
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return response()->json(['message' => 'Usuario creado exitosamente', 'user' => $user], 201);
    }

    /**
     * ✏️ Actualizar usuario
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        if (isset($validated['name'])) $user->name = $validated['name'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        // Si no tienes estas columnas en BD, comenta las siguientes 2 líneas:
        if (isset($validated['phone'])) $user->phone = $validated['phone'];
        if (isset($validated['address'])) $user->address = $validated['address'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['message' => 'Usuario actualizado', 'user' => $user]);
    }

    /**
     * 🗑️ Eliminar usuario
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'Usuario no encontrado'], 404);
        
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}