<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatbotQuestion;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        // Paso 1: Obtener y normalizar la pregunta
        $userQuestion = $this->normalizeText($request->input('message'));

        Log::info('Pregunta normalizada: ' . $userQuestion);

        // Paso 2: Normalizar también las preguntas de la BD y buscar coincidencia
        $match = ChatbotQuestion::get()->first(function ($item) use ($userQuestion) {
            return $this->normalizeText($item->question) === $userQuestion;
        });

        Log::info('Resultado encontrado:', ['respuesta' => optional($match)->answer]);

        // Paso 3: Responder
        if ($match) {
            return response()->json(['response' => $match->answer]);
        } else {
            return response()->json(['response' => 'Lo siento, no tengo una respuesta para eso.']);
        }
    }

    // Función para normalizar texto (elimina acentos, signos y espacios extra)
    private function normalizeText($text)
    {
        $text = strtolower(trim($text));
        $text = str_replace(['¿', '?', '¡', '!'], '', $text);

        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i',
            'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i',
            'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u',
            'ñ' => 'n', 'Ñ' => 'n',
        ];

        return strtr($text, $replacements);
    }
}
