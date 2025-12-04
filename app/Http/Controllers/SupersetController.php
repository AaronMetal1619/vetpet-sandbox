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
        
        // --- LIMPIEZA ROBUSTA DE URL ---
        $rawUrl = config('services.superset.frontend_url');
        // Quitamos protocolo y barras finales para obtener solo el dominio limpio
        $domainOnly = str_replace(['https://', 'http://'], '', $rawUrl);
        $domainOnly = rtrim($domainOnly, '/');
        
        // Preset a veces es caprichoso. Si en "Allowed Domains" pusiste solo el dominio,
        // el Referer debe coincidir. A veces funciona mejor enviando la URL completa (con https).
        // PERO el error dice "does not match", así que probemos enviando SOLO EL DOMINIO
        // que es lo que tienes configurado en la captura de pantalla.
        $refererToSend = $domainOnly; 
        // --------------------------------

        $apiKey = config('services.superset.preset_api_key');
        $apiSecret = config('services.superset.preset_api_secret');

        Log::info("🌍 MODALIDAD: $driver");
        Log::info("🔗 REFERER ENVIADO A PRESET: $refererToSend"); // <--- Esto aparecerá en los logs de Render

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
            }
            else {
                // Modo local...
                $accessToken = '...'; // (Tu código local)
            }

            // === OBTENER GUEST TOKEN ===
            $guestTokenResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'Referer' => $refererToSend, // Usamos la variable limpia
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
                // Logueamos lo que enviamos y lo que recibimos para depurar
                Log::error("❌ Preset Error. Enviado Referer: '$refererToSend'. Respuesta: " . $guestTokenResponse->body());
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