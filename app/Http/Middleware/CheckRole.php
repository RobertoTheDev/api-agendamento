<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Não autenticado.',
                'error' => 'Token de acesso requerido.',
            ], 401);
        }

        $user = $request->user();
        
        // Check if user is suspended
        if ($user->hasActiveSuspension()) {
            return response()->json([
                'message' => 'Acesso negado.',
                'error' => 'Usuário suspenso. Entre em contato com a administração.',
            ], 403);
        }

        // Check if user has required role
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Acesso negado.',
                'error' => 'Você não tem permissão para acessar este recurso.',
                'required_roles' => $roles,
                'user_role' => $user->role,
            ], 403);
        }

        return $next($request);
    }
}
