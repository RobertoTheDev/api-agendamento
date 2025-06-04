<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsGestor
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'gestor') {
            return $next($request); // Se for gestor, passa para a próxima rota
        }

        return redirect('/dashboard')->with('error', 'Acesso não autorizado.'); // Se não for gestor, redireciona
    }
}

