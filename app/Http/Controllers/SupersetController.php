<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupersetController extends Controller
{
    public function getGuestToken()
    {
        // 1. Usamos config() en lugar de env() para producción
        $driver = config('services.superset.driver', 'local');
        $supersetUrl = rtrim(config('services.superset.url', 'https://4169f60d.us1a.app.preset.io'), '/');
        $dashboardId = config('services.superset.dashboard_id');
        $frontendUrl = config('services.superset.frontend_url'); // Importante para el Referer

        // Credenciales
        $apiKey = config('services.superset.preset_api_key');
        $apiSecret = config('services.superset.preset_api_secret');

        Log::info("\n🌍 --- GENERANDO TOKEN MODO: " . strtoupper($driver) . " ---");

        try {
            $accessToken = null;

            // === ESCENARIO 1: MODO PRESET (PRODUCCIÓN / RENDER) ===
            if ($driver === 'preset') {
                if (!$apiKey || !$apiSecret) {
                    throw new \Exception("Faltan las credenciales de Preset en las variables de entorno.");
                }

                $response = Http::post('https://api.app.preset.io/v1/auth/', [
                    'name' => $apiKey,
                    'secret' => $apiSecret,
                ]);

                if ($response->failed()) {
                    throw new \Exception("Fallo Auth Preset: " . $response->body());
                }

                $accessToken = $response->json()['payload']['access_token'];
                Log::info("✅ Auth Preset OK.");
            }
            // === ESCENARIO 2: MODO LOCAL ===
            else {
                // ... (Tu código local sigue igual)
                $response = Http::post("$supersetUrl/api/v1/security/login", [
                    'username' => 'admin', 'password' => 'admin', 'provider' => 'db', 'refresh' => true,
                ]);
                $accessToken = $response->json()['access_token'];
            }

            // === SOLICITAR GUEST TOKEN ===
            Log::info("🎫 Solicitando Guest Token para Dashboard ID: $dashboardId");

            $guestTokenResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'Referer' => $frontendUrl, // CRUCIAL
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
                Log::error("❌ Preset rechazó el Guest Token: " . $guestTokenResponse->body());
                throw new \Exception("Fallo Guest Token. Verifica 'Allowed Domains' en Preset. Respuesta: " . $guestTokenResponse->body());
            }

            return response()->json([
                'token' => $guestTokenResponse->json()['token'],
                'supersetDomain' => $supersetUrl,
                'dashboardId' => $dashboardId
            ]);

        } catch (\Exception $e) {
            Log::error("❌ ERROR CRÍTICO SUPERSET: " . $e->getMessage());
            return response()->json(['error' => 'Error backend', 'details' => $e->getMessage()], 500);
        }
    }
}