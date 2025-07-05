<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatbotQuestion;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $userQuestion = $this->normalizeText($request->input('message'));
        Log::info('Pregunta normalizada: ' . $userQuestion);

        $questions = ChatbotQuestion::all();
        $bestMatch = null;
        $highestSimilarity = 0;

        foreach ($questions as $question) {
            $normalized = $this->normalizeText($question->question);
            similar_text($userQuestion, $normalized, $percent);

            if ($percent > $highestSimilarity) {
                $highestSimilarity = $percent;
                $bestMatch = $question;
            }
        }

        Log::info('Similitud máxima encontrada: ' . $highestSimilarity . '%');

        if ($bestMatch && $highestSimilarity >= 70) {
            return response()->json(['response' => $bestMatch->answer]);
        } else {
            return response()->json(['response' => 'Lo siento, no entendí la pregunta. ¿Puedes reformularla?']);
        }
    }

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
