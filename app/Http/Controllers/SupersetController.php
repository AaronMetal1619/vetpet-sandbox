<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupersetController extends Controller
{
    public function getGuestToken()
    {
        // Leemos si estamos en modo 'preset' o 'local' desde el .env
        $driver = env('SUPERSET_DRIVER', 'local');

        // Leemos la URL y el ID del dashboard
        $supersetUrl = env('SUPERSET_URL', 'https://4169f60d.us1a.app.preset.io');
        $dashboardId = env('SUPERSET_DASHBOARD_ID');

        Log::info("\n🌍 --- GENERANDO TOKEN MODO: " . strtoupper($driver) . " ---");

        try {
            $accessToken = null;

            // === ESCENARIO 1: MODO PRESET (PRODUCCIÓN / RENDER) ===
            if ($driver === 'preset') {
                // Preset requiere autenticación con API Key y Secret primero
                $response = Http::post('https://api.app.preset.io/v1/auth/', [
                    'name' => env('PRESET_API_KEY'),
                    'secret' => env('PRESET_API_SECRET'),
                ]);

                if ($response->failed()) {
                    throw new \Exception("Fallo Auth Preset: " . $response->body());
                }

                $accessToken = $response->json()['payload']['access_token'];
                Log::info("✅ Auth Preset OK.");
            }

            // === ESCENARIO 2: MODO LOCAL (DOCKER) ===
            else {
                $response = Http::post("$supersetUrl/api/v1/security/login", [
                    'username' => 'admin',
                    'password' => 'admin',
                    'provider' => 'db',
                    'refresh'  => true,
                ]);

                if ($response->failed()) {
                    throw new \Exception("Fallo Auth Local: " . $response->body());
                }

                $accessToken = $response->json()['access_token'];
                Log::info("✅ Auth Local OK.");
            }

            // === PASO COMÚN: PEDIR EL GUEST TOKEN ===
            Log::info("🎫 Solicitando Guest Token...");

            $guestTokenResponse = Http::withToken($accessToken)->post("$supersetUrl/api/v1/security/guest_token/", [
                'user' => [
                    'username' => 'guest',
                    'first_name' => 'Visitante',
                    'last_name' => 'Web',
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

            // Enviamos al Frontend todo lo que necesita
            return response()->json([
                'token' => $guestTokenResponse->json()['token'],
                'supersetDomain' => $supersetUrl, // Así React sabe si conectar a Preset o Localhost
                'dashboardId' => $dashboardId
            ]);
        } catch (\Exception $e) {
            Log::error("❌ ERROR SUPERSET: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
