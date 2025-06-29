<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'data'    => $user
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        try {
            $token = $this->authService->login($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'access_token' => $token,
                    'token_type'   => 'bearer',
                    'expires_in'   => JWTAuth::factory()->getTTL() * 60,
                    'user'         => new UserResource(JWTAuth::user())
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }
    }

    public function logout()
    {
        $this->authService->logout();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
            'data' => null
        ]);
    }

    public function me()
    {
        return response()->json([
            'success' => true,
            'message' => 'Authenticated user fetched successfully.',
            'data' => new UserResource(JWTAuth::user())
        ]);
    }
}
