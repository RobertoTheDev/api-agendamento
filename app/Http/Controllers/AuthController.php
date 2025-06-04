<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    // Registro de usuário
    public function register(Request $request)
    {
        // Validação dos dados
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:professor,gestor,admin',
        ]);

        // Criando usuário
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        if (!$user) {
            return response()->json(['error' => 'Erro ao criar usuário.'], 500);
        }

        // URL de redirecionamento baseada no papel do usuário
        $redirectUrl = match ($user->role) {
            'gestor'    => route('dashboard.gestor'),
            'professor' => route('dashboard.professor'),
            default     => route('dashboard'),
        };

        return response()->json([
            'message'       => 'Usuário registrado com sucesso.',
            'user'          => $user,
            'redirect_url'  => $redirectUrl
        ], 201);
    }

    // Login do usuário
    public function login(Request $request)
    {
        // Validação das credenciais
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'role'     => 'required|in:professor,gestor'
        ]);

        // Verifica se o usuário existe antes de autenticar
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Credenciais inválidas.'], 401);
        }

        // Verifica se o papel corresponde ao cadastrado no usuário
        if ($user->role !== $credentials['role']) {
            return response()->json(['error' => 'O tipo de usuário não corresponde ao registrado.'], 403);
        }

        // Impede login de "admin"
        if ($user->role === 'admin') {
            return response()->json(['error' => 'Admins não podem logar diretamente.'], 403);
        }

        // Autentica o usuário e gera token
        Auth::login($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        // Redireciona conforme o papel do usuário
        $redirectUrl = match ($user->role) {
            'gestor'    => route('dashboard.gestor'),
            'professor' => route('dashboard.professor'),
            default     => route('dashboard'),
        };

        return response()->json([
            'message'       => 'Login realizado com sucesso.',
            'user'          => $user,
            'token'         => $token,
            'redirect_url'  => $redirectUrl
        ], 200);
    }

    // Logout do usuário
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
            Auth::logout();
        }

        return response()->json(['message' => 'Logout realizado com sucesso.'], 204);
    }

    // Enviar e-mail para redefinição de senha
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'Se o e-mail existir no sistema, um link de redefinição será enviado.'
        ], 200);
    }
}
