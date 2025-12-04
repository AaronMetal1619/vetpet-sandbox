<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MascotaController extends Controller
{
    /**
     * Registra una nueva mascota para el usuario autenticado.
     * POST /api/mascotas
     */
    public function store(Request $request)
    {
        // 1. Validar la petición
        $request->validate([
            'nombre' => 'required|string|max:255',
            'especie' => 'required|string|max:255',
            'raza' => 'required|string|max:255',
            'genero' => 'required|string|in:Macho,Hembra',
            'edad' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:255',
            'peso' => 'nullable|string|max:255',
            'alergias' => 'nullable|boolean',
            'detalle_alergias' => 'nullable|string',
            'historial_medico' => 'nullable|string',
            'veterinario_encargado' => 'nullable|string|max:255',
            'foto' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // CRÍTICO: Aseguramos que 'user_id' no se tome del request y sea inyectado por Auth.
            $data = $request->except('user_id');
            $data['user_id'] = Auth::id();

            // Procesar y guardar la imagen usando el Storage Facade (Mejor Práctica Laravel)
            if ($request->hasFile('foto')) {
                // Guarda en storage/app/public/pets y retorna la ruta relativa
                $path = $request->file('foto')->store('pets', 'public');
                $data['foto'] = $path;
            }

            $mascota = Mascota::create($data);

            return response()->json([
                "message" => "Mascota registrada correctamente",
                "mascota" => $mascota
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error interno al registrar la mascota",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista todas las mascotas del usuario autenticado.
     * GET /api/mascotas
     */
    public function index()
    {
        try {
            // Seguridad: Traer solo las mascotas del usuario logueado
            $mascotas = Mascota::where('user_id', Auth::id())->get();

            // Retornar siempre un array, aunque esté vacío, para facilitar el manejo en React
            if ($mascotas->isEmpty()) {
                return response()->json([], 200);
            }

            return response()->json($mascotas);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al obtener la lista de mascotas",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra una mascota específica por ID, verificando que pertenezca al usuario.
     * GET /api/mascotas/{id}
     */
    public function show($id)
    {
        try {
            // Seguridad: Buscar por ID y por user_id para garantizar la propiedad
            $mascota = Mascota::where('user_id', Auth::id())->findOrFail($id);
            return response()->json($mascota);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Mascota no encontrada o no autorizada'], 404);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al obtener mascota",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza la mascota, verificando propiedad y manejando la foto.
     * PUT /api/mascotas/{id} (Se recibe como POST con _method=PUT)
     */
    public function update(Request $request, $id)
    {
        // Validar solo el campo de foto si está presente, el resto son opcionales
        $request->validate(['foto' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048']);

        try {
            // Seguridad: Buscar por ID y user_id para garantizar la propiedad
            $mascota = Mascota::where('user_id', Auth::id())->findOrFail($id);

            // Obtener todos los datos excepto user_id
            $data = $request->except(['user_id']);

            // Gestión de Archivos: Eliminar la foto antigua si se sube una nueva
            if ($request->hasFile('foto')) {
                if ($mascota->foto && Storage::disk('public')->exists($mascota->foto)) {
                    // Borrar el archivo anterior
                    Storage::disk('public')->delete($mascota->foto);
                }

                // Guardar el nuevo archivo
                $path = $request->file('foto')->store('pets', 'public');
                $data['foto'] = $path;
            }

            // Actualizar la instancia de la mascota
            $mascota->update($data);

            return response()->json([
                "message" => "Mascota actualizada",
                "mascota" => $mascota
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Mascota no encontrada o no autorizada'], 404);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al actualizar mascota",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina la mascota y su foto asociada, verificando propiedad.
     * DELETE /api/mascotas/{id}
     */
    public function destroy($id)
    {
        try {
            // Seguridad: Buscar por ID y user_id para garantizar la propiedad
            $mascota = Mascota::where('user_id', Auth::id())->findOrFail($id);

            // Gestión de Archivos: Eliminar la foto asociada antes de eliminar el registro
            if ($mascota->foto && Storage::disk('public')->exists($mascota->foto)) {
                Storage::disk('public')->delete($mascota->foto);
            }

            $mascota->delete();

            return response()->json(['message' => 'Mascota eliminada correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Mascota no encontrada o no autorizada'], 404);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al eliminar mascota",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
