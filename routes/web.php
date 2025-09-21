<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ManagementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Rota raiz
Route::get('/', function () {
    return redirect('/login');
});

// Página de login (sem middleware de autenticação)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Dashboards (sem middleware para páginas web)
Route::get('/dashboard/professor', function () {
    return view('dashboard.professor');
})->name('dashboard.professor');

Route::get('/dashboard/gestor', function () {
    return view('dashboard.gestor');
})->name('dashboard.gestor');

// Rotas de autenticação web
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return response()->json($request->user());
        });
    });
});
