<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatbotQuestion;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $userQuestion = strtolower(trim($request->input('message')));

        $match = ChatbotQuestion::whereRaw('LOWER(question) = ?', [$userQuestion])->first();

        if ($match) {
            return response()->json(['response' => $match->answer]);
        } else {
            return response()->json(['response' => 'Lo siento, no tengo una respuesta para eso.']);
        }
    }
}
