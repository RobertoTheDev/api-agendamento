<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfessorDashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.professor'); // Certifique-se de que a view existe em resources/views/dashboard/professor.blade.php
    }
}
