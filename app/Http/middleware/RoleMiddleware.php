<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Manipula a requisição e verifica o papel do usuário.
     */
    public function handle(Request $request, Closure $next, $roles)
    {
        // Obtém o usuário autenticado via Sanctum
        $user = $request->user();

        // Se não estiver autenticado, retorna erro
        if (!$user) {
            return response()->json([
                'message' => 'Acesso negado. O usuário não está autenticado.'
            ], 401);
        }

        // Transforma os papéis permitidos em array
        $allowedRoles = explode('|', $roles);

        // Verifica se o papel do usuário está na lista permitida
        if (!in_array($user->role, $allowedRoles)) {
            return response()->json([
                'message' => "Acesso negado. O usuário tem o papel '{$user->role}', mas esta rota exige: " . implode(', ', $allowedRoles)
            ], 403);
        }

        // Permite a requisição continuar se o papel for válido
        return $next($request);
    }
}
