<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupersetController extends Controller
{
    public function getGuestToken()
    {
        // 1. Leemos las variables del .env (sirve para local y render)
        $driver = env('SUPERSET_DRIVER', 'local'); // 'local' o 'preset'
        $supersetUrl = env('SUPERSET_URL', 'http://localhost:8088');
        $dashboardId = env('SUPERSET_DASHBOARD_ID');

        Log::info("\n🌍 --- GENERANDO TOKEN MODO: " . strtoupper($driver) . " ---");

        try {
            $accessToken = null;

            // === CASO A: MODO PRESET (PRODUCCIÓN) ===
            if ($driver === 'preset') {
                // En Preset, primero nos autenticamos contra su API global para obtener el token JWT
                $response = Http::post('https://api.app.preset.io/v1/auth/', [
                    'name' => env('PRESET_API_KEY'),
                    'secret' => env('PRESET_API_SECRET'),
                ]);

                if ($response->failed()) throw new \Exception("Fallo Auth Preset: " . $response->body());
                $accessToken = $response->json()['payload']['access_token'];

                Log::info("✅ Auth Preset OK.");
            }

            // === CASO B: MODO LOCAL (DOCKER) ===
            else {
                $response = Http::post("$supersetUrl/api/v1/security/login", [
                    'username' => env('SUPERSET_USERNAME', 'admin'),
                    'password' => env('SUPERSET_PASSWORD', 'admin'),
                    'provider' => 'db',
                    'refresh'  => true,
                ]);

                if ($response->failed()) throw new \Exception("Fallo Auth Local: " . $response->body());
                $accessToken = $response->json()['access_token'];
                Log::info("✅ Auth Local OK.");
            }

            // === 2. OBTENER EL GUEST TOKEN (Igual para ambos, solo cambia la URL base) ===
            Log::info("🎫 Solicitando Guest Token para Dashboard: $dashboardId");

            // Nota: En Preset la URL suele ser /v1/security/guest_token/, igual que en Superset
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

            return response()->json([
                'token' => $guestTokenResponse->json()['token'],
                // Enviamos también estos datos al front para que no estén hardcodeados en React
                'supersetDomain' => $supersetUrl,
                'dashboardId' => $dashboardId
            ]);
        } catch (\Exception $e) {
            Log::error("❌ ERROR SUPERSET: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
