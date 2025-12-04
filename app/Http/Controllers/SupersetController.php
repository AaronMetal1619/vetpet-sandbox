<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupersetController extends Controller
{
    public function getGuestToken()
    {
        // Configuración
        $driver = config('services.superset.driver', 'local');
        $supersetUrl = rtrim(config('services.superset.url', 'https://4169f60d.us1a.app.preset.io'), '/');
        $dashboardId = config('services.superset.dashboard_id');
        $apiKey = config('services.superset.preset_api_key');
        $apiSecret = config('services.superset.preset_api_secret');

        // --- CUMPLIENDO LA DOCUMENTACIÓN ---
        // La doc dice: "Asegúrese de incluir el protocolo (https://)"
        // Ponemos la URL exacta que pusiste en Preset.
        $refererSeguro = 'https://vetpetfront.onrender.com';
        // -----------------------------------

        Log::info("🌍 MODO: $driver");
        Log::info("🔗 REFERER ENVIADO: $refererSeguro");

        try {
            $accessToken = null;

            // 1. AUTENTICACIÓN
            if ($driver === 'preset') {
                if (empty($apiKey) || empty($apiSecret)) {
                    throw new \Exception("Credenciales vacías.");
                }

                $response = Http::post('https://api.app.preset.io/v1/auth/', [
                    'name' => $apiKey,
                    'secret' => $apiSecret,
                ]);

                if ($response->failed()) {
                    throw new \Exception("Fallo Auth Preset: " . $response->body());
                }

                $accessToken = $response->json()['payload']['access_token'];
            } else {
                $accessToken = '...'; 
            }

            // 2. OBTENER GUEST TOKEN
            // La documentación dice que el backend solicita el token.
            // El header 'Referer' es crucial aquí.
            $guestTokenResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'Referer' => $refererSeguro, // Enviamos con HTTPS
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post("$supersetUrl/api/v1/security/guest_token/", [
                    'user' => [
                        'username' => 'guest',
                        'first_name' => 'VetPet',
                        'last_name' => 'User',
                    ],
                    'resources' => [[
                        'type' => 'dashboard',
                        'id'   => $dashboardId,
                    ]],
                    'rls' => [],
                ]);

            if ($guestTokenResponse->failed()) {
                Log::error("❌ Preset Error: " . $guestTokenResponse->body());
                throw new \Exception("Fallo Guest Token: " . $guestTokenResponse->body());
            }

            return response()->json([
                'token' => $guestTokenResponse->json()['token'],
                'supersetDomain' => $supersetUrl,
                'dashboardId' => $dashboardId
            ]);

        } catch (\Exception $e) {
            Log::error("❌ EXCEPCIÓN: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}