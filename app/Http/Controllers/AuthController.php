public function login(Request $request)
    {
        try {
            // 1. Validar entrada
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            // 2. Intentar autenticación
            if (!Auth::attempt($credentials)) {
                return response()->json(['message' => 'Credenciales incorrectas (Auth::attempt falló)'], 401);
            }

            // 3. Obtener el usuario
            $user = Auth::user();

            // 4. Intentar generar el token (Aquí es donde suele explotar)
            // Borramos tokens anteriores primero para limpiar
            try {
                $user->tokens()->delete();
                $token = $user->createToken('auth_token')->plainTextToken;
            } catch (\Exception $e) {
                // Si falla aquí, es problema de la tabla personal_access_tokens
                throw new \Exception("Error con Sanctum/Tokens: " . $e->getMessage());
            }

            return response()->json([
                'message' => 'Login exitoso',
                'token' => $token,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            // 🔥 ESTO ES LO QUE NECESITAMOS VER 🔥
            return response()->json([
                'status' => 'error_fatal',
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ], 500); 
        }
    }