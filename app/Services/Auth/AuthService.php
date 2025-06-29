<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('student');

        return $user;
    }

    public function login(array $credentials): string
    {
        if (!$token = Auth::attempt($credentials)) {
            throw new \Exception('Unauthorized');
        }

        return $token;
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function me(): User
    {
        return Auth::user();
    }
}
