<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupersetController extends Controller
{
    public function getGuestToken()
    {
        // Configuración básica
        $driver = config('services.superset.driver', 'local');
        $supersetUrl = rtrim(config('services.superset.url', 'https://4169f60d.us1a.app.preset.io'), '/');
        $dashboardId = config('services.superset.dashboard_id');
        $apiKey = config('services.superset.preset_api_key');
        $apiSecret = config('services.superset.preset_api_secret');

        // --- SOLUCIÓN A PRUEBA DE BALAS ---
        // Escribimos la URL manualmente para asegurar que sea EXACTA a la de Preset
        // Debe coincidir letra por letra con "Allowed Domains"
        $refererFijo = 'https://vetpetfront.onrender.com';
        // ----------------------------------

        Log::info("🌍 MODO: $driver");
        Log::info("🔗 INTENTANDO AUTH CON REFERER: $refererFijo");

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
                $accessToken = '...'; // Modo local
            }

            // === PETICIÓN DEL GUEST TOKEN ===
            $guestTokenResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'Referer' => $refererFijo, // Enviamos el valor fijo con HTTPS
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
                // Logueamos el error completo para verlo en Render
                Log::error("❌ ERROR PRESET: " . $guestTokenResponse->body());
                throw new \Exception("Fallo Guest Token. Verifica que '$refererFijo' esté en Allowed Domains.");
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