<?php  

namespace App\Http\Controllers;  

use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth; 
use App\Models\User;  

class AuthController extends Controller 
{     
    public function login(Request $request)     
    {         
        $request->validate([             
            'email' => 'required|email',             
            'password' => 'required'         
        ]);              

        if (!Auth::attempt($request->only('email', 'password'))) {             
            return response()->json(['message' => 'Credenciales incorrectas'], 401);         
        }              

        $user = Auth::user();         
        $token = $user->createToken('auth_token')->plainTextToken;              

        return response()->json([             
            'message' => 'Login exitoso',             
            'token' => $token,             
            'user' => $user         
        ]);     
    }     

    public function register(Request $request)     
    {         
        $request->validate([             
            'name' => 'required|string|max:255',             
            'email' => 'required|email|unique:users',             
            'password' => 'required|min:6',         
        ]);              

        $user = User::create([             
            'name' => $request->name,             
            'email' => $request->email,             
            'password' => bcrypt($request->password),         
        ]);              

        $token = $user->createToken('auth_token')->plainTextToken;              

        return response()->json([             
            'message' => 'Usuario registrado exitosamente',             
            'token' => $token,             
            'user' => $user         
        ]);     
    }           

    public function logout(Request $request)     
    {         
        $request->user()->tokens()->delete();         
        return response()->json(['message' => 'Cierre de sesión exitoso']);     
    }      

    public function me(Request $request)     
    {         
        return response()->json($request->user());     
    } 
}
