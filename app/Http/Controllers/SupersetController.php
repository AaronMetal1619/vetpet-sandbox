<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupersetController extends Controller
{
    public function getGuestToken()
    {
        $driver = config('services.superset.driver', 'local');
        $supersetUrl = rtrim(config('services.superset.url', 'https://4169f60d.us1a.app.preset.io'), '/');
        $dashboardId = config('services.superset.dashboard_id');
        
        // --- CORRECCIÓN FINAL: USAMOS LA URL COMPLETA ---
        // Preset necesita que el Referer sea una URL válida (con https://)
        // Luego ellos internamente extraen el dominio para validarlo.
        $frontendUrl = config('services.superset.frontend_url'); 
        
        // Por seguridad, asegurémonos de que no tenga barra al final
        $frontendUrl = rtrim($frontendUrl, '/');
        // ------------------------------------------------

        $apiKey = config('services.superset.preset_api_key');
        $apiSecret = config('services.superset.preset_api_secret');

        Log::info("🌍 MODALIDAD: $driver");
        Log::info("🔗 URL PRESET: $supersetUrl");
        Log::info("🔗 REFERER ENVIADO: $frontendUrl"); // <--- Debe salir https://vetpetfront.onrender.com

        try {
            $accessToken = null;

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
                // Modo local
                $accessToken = '...'; 
            }

            // === OBTENER GUEST TOKEN ===
            $guestTokenResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'Referer' => $frontendUrl, // Enviamos https://vetpetfront.onrender.com
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
                Log::error("❌ Preset Error Body: " . $guestTokenResponse->body());
                throw new \Exception("Fallo Guest Token: " . $guestTokenResponse->body());
            }

            return response()->json([
                'token' => $guestTokenResponse->json()['token'],
                'supersetDomain' => $supersetUrl,
                'dashboardId' => $dashboardId
            ]);

        } catch (\Exception $e) {
            Log::error("❌ ERROR SUPERSET: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}