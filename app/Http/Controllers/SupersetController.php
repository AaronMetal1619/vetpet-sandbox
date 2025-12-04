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
        
        // --- CORRECCIÓN AQUÍ ---
        // Obtenemos la URL del .env
        $rawFrontendUrl = config('services.superset.frontend_url'); 
        
        // Le quitamos 'https://' y 'http://' para dejar solo el dominio 'vetpetfront.onrender.com'
        // Esto es para que coincida EXACTAMENTE con lo que pusiste en la lista blanca de Preset.
        $frontendUrl = str_replace(['https://', 'http://'], '', $rawFrontendUrl); 
        // -----------------------

        $apiKey = config('services.superset.preset_api_key');
        // ... resto del código ...
        $apiSecret = config('services.superset.preset_api_secret');

        Log::info("\n🌍 --- GENERANDO TOKEN MODO: " . strtoupper($driver) . " ---");

        try {
            $accessToken = null;

            // === MODO PRESET ===
            if ($driver === 'preset') {
                // Validación extra para depurar
                if (empty($apiKey) || empty($apiSecret)) {
                    throw new \Exception("Credenciales vacías. Revisa config/services.php y cache.");
                }

                $response = Http::post('https://api.app.preset.io/v1/auth/', [
                    'name' => $apiKey,
                    'secret' => $apiSecret,
                ]);

                if ($response->failed()) {
                    throw new \Exception("Fallo Auth Preset: " . $response->body());
                }

                $accessToken = $response->json()['payload']['access_token'];
            }
            // === MODO LOCAL ===
            else {
                $response = Http::post("$supersetUrl/api/v1/security/login", [
                    'username' => 'admin', 'password' => 'admin', 'provider' => 'db', 'refresh' => true,
                ]);
                $accessToken = $response->json()['access_token'];
            }

            // === OBTENER GUEST TOKEN ===
            $guestTokenResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'Referer' => $frontendUrl, // CRUCIAL: Debe coincidir con 'Allowed Domains'
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