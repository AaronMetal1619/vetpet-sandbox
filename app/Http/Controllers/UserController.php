<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        if ($request->has('role')) {
            if ($request->role === 'veterinaria') {
                $query->where('role', 'partner')->where('partner_type', 'veterinaria');
            } else {
                $query->where('role', $request->role);
            }
        }
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|string',
            'partner_type' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            // ✅ NUEVOS CAMPOS
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'partner_type' => $validated['partner_type'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            // ✅ GUARDAR COORDENADAS
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        return response()->json(['message' => 'Creado exitosamente', 'user' => $user], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'No encontrado'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            // ✅ NUEVOS CAMPOS
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if (isset($validated['name'])) $user->name = $validated['name'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        if (isset($validated['phone'])) $user->phone = $validated['phone'];
        if (isset($validated['address'])) $user->address = $validated['address'];
        // ✅ ACTUALIZAR COORDENADAS
        if (isset($validated['latitude'])) $user->latitude = $validated['latitude'];
        if (isset($validated['longitude'])) $user->longitude = $validated['longitude'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        return response()->json(['message' => 'Actualizado', 'user' => $user]);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'No encontrado'], 404);
        $user->delete();
        return response()->json(['message' => 'Eliminado']);
    }
    // Método especial para actualizar perfil con FOTO
    public function updateProfile(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'Usuario no encontrado'], 404);

        // 1. Validación (Permitimos que los campos sean opcionales)
        $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validar imagen
        ]);

        // 2. Actualizar datos de texto si vienen en la petición
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('phone')) $user->phone = $request->phone;

        // 3. Lógica para la IMAGEN (Lo que faltaba)
        if ($request->hasFile('profile_picture')) {
            // Guardamos en la carpeta 'users' dentro de public
            $path = $request->file('profile_picture')->store('users', 'public');
            // Guardamos la URL completa
            $user->profile_picture = url('storage/' . $path);
        }

        $user->save();

        // 4. Retornamos la respuesta exacta que tu React espera
        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'user' => $user
        ], 200);
    }
}