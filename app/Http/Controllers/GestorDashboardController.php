<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class GestorDashboardController extends Controller
{
    public function index()
    {
        // Busca os professores para exibir no painel do gestor
        $professores = User::where('role', 'professor')->select('id', 'name', 'email')->get();

        return view('dashboard.gestor');
    }
}
