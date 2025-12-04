<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupersetController extends Controller
{
    public function getGuestToken()
    {
        // 1. Configuración Inicial
        $driver = env('SUPERSET_DRIVER', 'local');
        // Aseguramos que no haya barras al final de la URL
        $supersetUrl = rtrim(env('SUPERSET_URL', 'https://4169f60d.us1a.app.preset.io'), '/');
        $dashboardId = env('SUPERSET_DASHBOARD_ID');

        // CRUCIAL: Esta URL debe coincidir con la lista "Allowed Domains" en Preset
        $frontendUrl = env('FRONTEND_URL', 'https://vetpetfront.onrender.com');

        Log::info("\n🌍 --- GENERANDO TOKEN MODO: " . strtoupper($driver) . " ---");

        try {
            $accessToken = null;

            // === ESCENARIO 1: MODO PRESET (PRODUCCIÓN / RENDER) ===
            if ($driver === 'preset') {
                // Preset requiere autenticación con API Key y Secret
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

            // === PASO COMÚN: PEDIR EL GUEST TOKEN (AQUÍ ESTABA EL ERROR) ===
            Log::info("🎫 Solicitando Guest Token para Dashboard ID: $dashboardId");

            // Solicitud corregida con Header REFERER
            $guestTokenResponse = Http::withToken($accessToken)
                ->withHeaders([
                    // ESTO ES LO QUE FALTABA: Preset exige saber quién pide el token
                    'Referer' => $frontendUrl,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post("$supersetUrl/api/v1/security/guest_token/", [
                    'user' => [
                        'username' => 'guest',
                        'first_name' => 'Visitante',
                        'last_name' => 'Web',
                    ],
                    'resources' => [[
                        'type' => 'dashboard',
                        'id'   => $dashboardId,
                    ]],
                    'rls' => [], // Row Level Security (vacío si no se usa)
                ]);

            if ($guestTokenResponse->failed()) {
                // Logueamos el error exacto que devuelve Preset (ej. Referer missing)
                Log::error("❌ Preset rechazó el Guest Token: " . $guestTokenResponse->body());
                throw new \Exception("Fallo Guest Token: " . $guestTokenResponse->body());
            }

            // Enviamos al Frontend todo lo que necesita
            return response()->json([
                'token' => $guestTokenResponse->json()['token'],
                'supersetDomain' => $supersetUrl,
                'dashboardId' => $dashboardId
            ]);
        } catch (\Exception $e) {
            Log::error("❌ ERROR CRÍTICO SUPERSET: " . $e->getMessage());

            // Devolvemos el error detallado para verlo en la consola del navegador (Network Tab)
            return response()->json([
                'error' => 'Error obteniendo token de visualización',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
