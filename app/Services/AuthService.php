<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): User
    {
        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
            "role" => $data["role"],
            "phone" => $data["phone"] ?? null,
            "profile_picture" => $data["profile_picture"] ?? null,
        ]);

        return $user;
    }

    public function login(array $credentials): array
    {
        $user = User::where("email", $credentials["email"])->first();

        if (!$user || !Hash::check($credentials["password"], $user->password)) {
            throw ValidationException::withMessages([
                "email" => ["Credenciais inválidas."],
            ]);
        }

        if (isset($credentials["role"]) && $user->role !== $credentials["role"]) {
            throw ValidationException::withMessages([
                "role" => ["O tipo de usuário não corresponde ao registrado."],
            ]);
        }

        if ($user->hasActiveSuspension()) {
            throw ValidationException::withMessages([
                "email" => ["Usuário suspenso. Entre em contato com a administração."],
            ]);
        }

        if ($user->isAdmin()) {
            throw ValidationException::withMessages([
                "email" => ["Admins não podem fazer login via API."],
            ]);
        }

        Auth::login($user);
        $token = $user->createToken("auth_token")->plainTextToken;

        return [
            "user" => $user,
            "token" => $token,
            "redirect_url" => $this->getRedirectUrl($user),
        ];
    }

    public function logout(): void
    {
        $user = request()->user();
        if ($user) {
            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }
        }
    }

    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }

    public function getRedirectUrl(User $user): string
    {
        return match ($user->role) {
            User::ROLE_GESTOR => "/dashboard/gestor",
            User::ROLE_PROFESSOR => "/dashboard/professor", 
            User::ROLE_ADMIN => "/dashboard/admin",
            default => "/dashboard",
        };
    }
}
