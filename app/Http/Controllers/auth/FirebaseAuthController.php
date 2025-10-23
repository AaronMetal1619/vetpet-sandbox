<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Auth as FirebaseAuth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FirebaseAuthController extends Controller
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    public function handle(Request $request)
    {
        $request->validate(['idToken' => 'required|string']);
        $idToken = $request->input('idToken');

        try {
            // Verificar el ID token con Firebase Admin
            $verifiedToken = $this->firebaseAuth->verifyIdToken($idToken);
            $uid = $verifiedToken->claims()->get('sub'); // Firebase uid
            $email = $verifiedToken->claims()->get('email');
            $name = $verifiedToken->claims()->get('name') ?? $email;

            if (!$email) {
                return response()->json(['message' => 'No email provided by provider'], 422);
            }

            // Buscar usuario por firebase_uid o email
            $user = User::where('firebase_uid', $uid)->orWhere('email', $email)->first();

            if (!$user) {
                // Crear nuevo usuario
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(\Illuminate\Support\Str::random(24)), // password random
                    'firebase_uid' => $uid,
                ]);
            } else {
                // si existe por email pero sin firebase_uid, enlazarlo
                if (!$user->firebase_uid) {
                    $user->firebase_uid = $uid;
                    $user->save();
                }
            }

            // Generar tu token (Sanctum / personal token)
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
            ]);
        } catch (\Kreait\Firebase\Exception\Auth\FailedToVerifyToken $e) {
            return response()->json(['message' => 'Invalid Firebase token', 'error' => $e->getMessage()], 401);
        } catch (\Throwable $e) {
            \Log::error('FirebaseAuth error: '.$e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}
