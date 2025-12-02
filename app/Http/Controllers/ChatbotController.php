<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cita;
use Illuminate\Support\Facades\Validator;

class ChatbotController extends Controller
{
    // 🧠 Procesar mensaje del chatbot
    public function handle(Request $request)
    {
        $text = strtolower($request->text ?? '');
        $location = $request->location ?? null;

        // Respuestas básicas
        if (str_contains($text, 'hola') || str_contains($text, 'ayuda')) {
            return response()->json([
                "answer" => "¡Hola! Soy tu asistente VetPet 😊  
Puedo darte información básica o ayudarte a agendar una cita.  
¿Qué necesitas?"
            ]);
        }

        if (str_contains($text, 'cita')) {
            return response()->json([
                "answer" => "Perfecto, puedo ayudarte a agendar una cita.  
¿Puedes decirme para qué día y hora la deseas?"
            ]);
        }

        if (str_contains($text, 'veterinaria')) {

            // usuario envió su ubicación
            if ($location) {
                return $this->nearestVet($location);
            }

            return response()->json([
                "answer" => "Puedo recomendarte la veterinaria más cercana si me autorizas tu ubicación 📍."
            ]);
        }

        return response()->json([
            "answer" => "No entendí muy bien, ¿podrías repetirlo?"
        ]);
    }

    // 📍 Veterinaria más cercana
    private function nearestVet($userLoc)
    {
        $vets = User::where('role', 'partner')
            ->where('partner_type', 'veterinaria')
            ->get();

        if ($vets->isEmpty()) {
            return response()->json([
                "answer" => "No encontré veterinarias registradas 😕."
            ]);
        }

        // Haversine
        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($vets as $vet) {
            if (!$vet->latitude || !$vet->longitude) continue;

            $distance = $this->distance(
                $userLoc['lat'], $userLoc['lng'],
                $vet->latitude, $vet->longitude
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $vet;
            }
        }

        if (!$nearest) {
            return response()->json([
                "answer" => "No encontré veterinarias con ubicación registrada."
            ]);
        }

        return response()->json([
            "answer" => "La veterinaria más cercana es **{$nearest->name}** 📍  
A {$minDistance} km aproximadamente."
        ]);
    }

    // Fórmula haversine
    private function distance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) ** 2;

        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    // 🗓️ Crear cita
    public function createAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nombre" => "required|string",
            "fecha"  => "required|date",
            "hora"   => "required",
            "veterinaria_id" => "required|exists:users,id"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "answer" => "Los datos enviados no son válidos.",
                "errors" => $validator->errors()
            ], 422);
        }

        $cita = Cita::create([
            "nombre" => $request->nombre,
            "fecha" => $request->fecha,
            "hora" => $request->hora,
            "user_id" => $request->veterinaria_id,
        ]);

        return response()->json([
            "answer" => "¡Listo! Tu cita ha sido registrada correctamente 📅✨",
            "data" => $cita
        ]);
    }
}
