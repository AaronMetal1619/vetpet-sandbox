<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cita;
use Illuminate\Support\Facades\Validator;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $text = strtolower($request->text ?? '');
        $location = $request->location ?? null;

        if (!$text) {
            return response()->json(["answer" => "No recibí ningún mensaje 🤔"]);
        }

        if (str_contains($text, 'hola') || str_contains($text, 'ayuda')) {
            return response()->json([
                "answer" => "¡Hola! Soy tu asistente VetPet 😊  
Puedo darte información o ayudarte a agendar una cita.  
¿Qué necesitas?"
            ]);
        }

        if (str_contains($text, 'cita')) {
            return response()->json([
                "answer" => "Perfecto 🗓️  
Dime tu **nombre**, **día** y **hora** para la cita."
            ]);
        }

        if (str_contains($text, 'veterinaria')) {

            if ($location) {
                return $this->nearestVet($location);
            }

            return response()->json([
                "answer" => "Para recomendarte la veterinaria más cercana, necesito tu ubicación 📍."
            ]);
        }

        return response()->json([
            "answer" => "No entendí muy bien 😅  
¿Puedes repetirlo?"
        ]);
    }

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
                "answer" => "No encontré veterinarias con ubicación válida."
            ]);
        }

        return response()->json([
            "answer" => "La veterinaria más cercana es **{$nearest->name}** 📍  
A aproximadamente **" . round($minDistance, 2) . " km**."
        ]);
    }

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

    public function createAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nombre" => "required|string",
            "dia" => "required|string",
            "hora" => "required|string"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "answer" => "Faltan datos para agendar la cita.  
Debes enviar: **nombre, día y hora**."
            ]);
        }

        $cita = Cita::create([
            "nombre" => $request->nombre,
            "dia" => $request->dia,
            "hora" => $request->hora
        ]);

        return response()->json([
            "answer" => "¡Cita registrada con éxito! 🎉  
Te espero el **{$cita->dia}** a las **{$cita->hora}**."
        ]);
    }
}
