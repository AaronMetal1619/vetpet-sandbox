<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupersetController extends Controller
{
    public function getGuestToken()
    {
        Log::info("\n🏠 --- MODO LOCAL (LARAVEL) ---");

        // ✅ 1. NUEVO UUID DEL DASHBOARD ACTUALIZADO
        $dashboardId = "7e1679bc-c9d4-4ac4-a0c1-16521659a5ed";

        // URL de tu Superset local
        // Si usas Docker y Laravel Sail, quizás necesites: 'http://host.docker.internal:8088'
        $supersetUrl = 'http://localhost:8088';

        try {
            // 2. LOGIN COMO ADMIN
            Log::info("1️⃣ Logueando en Superset Local...");

            $loginResponse = Http::post("$supersetUrl/api/v1/security/login", [
                'username' => 'admin',
                'password' => 'admin',
                'provider' => 'db',
                'refresh'  => true,
            ]);

            if ($loginResponse->failed()) {
                throw new \Exception("Fallo en Login: " . $loginResponse->body());
            }

            $accessToken = $loginResponse->json()['access_token'];
            Log::info("✅ Login Local OK.");

            // 3. PEDIR EL GUEST TOKEN
            Log::info("2️⃣ Solicitando pase para Dashboard: $dashboardId");

            $guestTokenResponse = Http::withToken($accessToken)->post("$supersetUrl/api/v1/security/guest_token/", [
                'user' => [
                    'username' => 'guest',
                    'first_name' => 'Visitante',
                    'last_name' => 'Local',
                ],
                'resources' => [
                    [
                        'type' => 'dashboard',
                        'id'   => $dashboardId,
                    ]
                ],
                'rls' => [],
            ]);

            if ($guestTokenResponse->failed()) {
                throw new \Exception("Fallo al obtener Guest Token: " . $guestTokenResponse->body());
            }

            Log::info("✅ ¡TOKEN LOCAL GENERADO! 🏠");

            // Devolvemos el token a tu React
            return response()->json([
                'token' => $guestTokenResponse->json()['token']
            ]);
        } catch (\Exception $e) {
            Log::error("❌ ERROR LOCAL: " . $e->getMessage());

            return response()->json([
                'error' => 'Fallo Local en Laravel',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
