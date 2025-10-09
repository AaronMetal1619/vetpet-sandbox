<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Exception;

class SocialiteController extends Controller
{
    // Redirige a Facebook
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    // Callback de Facebook
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            // Buscar si ya existe
            $user = User::where('facebook_id', $facebookUser->id)
                ->orWhere('email', $facebookUser->email)
                ->first();

            if (!$user) {
                // Crear usuario si no existe
                $user = User::create([
                    'name' => $facebookUser->name,
                    'email' => $facebookUser->email,
                    'facebook_id' => $facebookUser->id,
                    'password' => bcrypt(str()->random(16)), // clave aleatoria
                ]);
            }

            // Autenticar al usuario
            Auth::login($user);

            // Redirigir donde quieras (por ejemplo, al dashboard)
            return redirect('/dashboard');

        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Error al autenticar con Facebook');
        }
    }
}
