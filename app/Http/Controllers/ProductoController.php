<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index()
    {
        // Obtener todos los productos y devolver la URL de la imagen
        $productos = Producto::all()->map(function($producto) {
            $producto->imagen = asset('storage/' . $producto->imagen); // Generar la URL pública
            return $producto;
        });

        return response()->json($productos);
    }

    public function store(Request $request)
    {
        // Validación de los datos
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Subir la imagen si existe
        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        // Crear el producto
        $producto = Producto::create($data);

        // Agregar la URL pública de la imagen
        $producto->imagen = asset('storage/' . $producto->imagen);

        return response()->json($producto, 201);
    }

    public function show(Producto $producto)
    {
        // Generar la URL pública de la imagen
        $producto->imagen = asset('storage/' . $producto->imagen);
        return response()->json($producto);
    }

    public function update(Request $request, Producto $producto)
    {
        // Validación de los datos
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Subir una nueva imagen si es necesario
        if ($request->hasFile('imagen')) {
            // Eliminar la imagen anterior si existe
            if ($producto->imagen && Storage::exists('public/' . $producto->imagen)) {
                Storage::delete('public/' . $producto->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        // Actualizar el producto
        $producto->update($data);

        // Agregar la URL pública de la imagen
        $producto->imagen = asset('storage/' . $producto->imagen);

        return response()->json($producto);
    }

    public function destroy(Producto $producto)
    {
        // Verificar si el producto tiene una imagen
        if ($producto->imagen && Storage::exists('public/' . $producto->imagen)) {
            Storage::delete('public/' . $producto->imagen);
        }

        // Eliminar el producto
        $producto->delete();

        return response()->json(null, 204);
    }
}
