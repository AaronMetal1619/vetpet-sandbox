<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Exception;

class SocialiteController extends Controller
{
    public function redirectToFacebook()
    {
        // solicitar email explícitamente
        return Socialite::driver('facebook')->scopes(['email'])->stateless()->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            $fbUser = Socialite::driver('facebook')->stateless()->user();

            // Si no hay email, falla (Facebook puede no devolverlo)
            $email = $fbUser->getEmail();
            if (!$email) {
                return redirect('https://vetpetfront.onrender.com/login?error=NoEmailFromFacebook');
            }

            // Buscar por facebook_id o por email
            $user = User::where('facebook_id', $fbUser->getId())
                        ->orWhere('email', $email)
                        ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $fbUser->getName() ?? $email,
                    'email' => $email,
                    'facebook_id' => $fbUser->getId(),
                    'password' => bcrypt(str()->random(24)),
                ]);
            } else {
                // si encontró por email pero no tiene facebook_id, lo actualizamos
                if (!$user->facebook_id) {
                    $user->facebook_id = $fbUser->getId();
                    $user->save();
                }
            }

            // Crear token y devolver al frontend (puedes redirigir)
            $token = $user->createToken('authToken')->plainTextToken;

            // Redirige a tu frontend con el token en query (o guarda sesión)
            return redirect("https://vetpetfront.onrender.com/social-login-success?token={$token}");

        } catch (Exception $e) {
            \Log::error('FB login error: '.$e->getMessage());
            return redirect('https://vetpetfront.onrender.com/login?error=facebook_failed');
        }
    }
    // Nuevas funciones para Google
    public function redirectToGoogle()
        {
        // pedimos profile y email
        return Socialite::driver('google')
                        ->scopes(['openid','profile','email'])
                        ->stateless()
                        ->redirect();
        }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $email = $googleUser->getEmail();
            $googleId = $googleUser->getId();

            if (!$email) {
                // No email from Google — manejar como error
                return redirect('https://vetpetfront.onrender.com/login?error=no_email');
            }

            // Buscar por google_id o por email
            $user = User::where('google_id', $googleId)
                        ->orWhere('email', $email)
                        ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName() ?? $email,
                    'email' => $email,
                    'google_id' => $googleId,
                    'password' => bcrypt(str()->random(24)),
                ]);
            } else {
                // Si se encontró por email pero no tiene google_id, lo registramos
                if (!$user->google_id) {
                    $user->google_id = $googleId;
                    $user->save();
                }
            }

            // Crear token (sanctum/personal token)
            $token = $user->createToken('authToken')->plainTextToken;

            // Redirigir al frontend con token (o puedes responder JSON)
            return redirect("https://vetpetfront.onrender.com/social-login-success?token={$token}");

        } catch (Exception $e) {
            \Log::error('Google login error: '.$e->getMessage());
            return redirect('https://vetpetfront.onrender.com/login?error=google_failed');
        }
    }

}
