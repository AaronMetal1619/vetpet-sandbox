<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class PerfilController extends Controller
{
    public function update(Request $request)
{
    // Validación solo del nombre
    $data = $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $user = Auth::user();
    $user->name = $data['name'];
    $user->save();

    return response()->json([
        'message' => 'Perfil actualizado exitosamente.',
        'name' => $user->name
    ]);
}

}
