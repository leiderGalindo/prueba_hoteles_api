<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Contracts\Providers\JWT;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{    
    public function Login(Request $request): JsonResponse
    {
        // Obtenemos los datos ingresados
        $credentials = $request->only('email', 'password');

        if (! $token = auth(guard: 'api')->attempt($credentials)) {
            return response()->json([
                'error' => 'unauthorized',
            ], 401);
        }

        $respond = $this->respondWithToken($token);
        return response()->json($respond, 200);
    }

    public function Logout(Request $request): JsonResponse
    {

        try {
            auth()->logout();
    
            return response()->json([
                'message' => 'successfully logged out',
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Token not found',
            ], 401);
        }
    }

    public function respondWithToken($token)
    {
        return [
            'token' => $token,
            'type' => 'Bearer',
            'expires_in' => auth()->guard('api')->factory()->getTTL() * 60,
        ];
    }
}
