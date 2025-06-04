<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ManagementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Rotas de autenticação
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']); // Registrar usuário
    Route::post('/login', [AuthController::class, 'login']); // Login
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']); // Redefinir senha

    // Logout e obtenção de dados do usuário autenticado
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']); // Logout
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return response()->json($request->user()); // Retorna dados do usuário autenticado
    });
});

// Rotas para usuários (professores e gestores)
Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']); // Listar todos os usuários
    Route::post('/', [UserController::class, 'store']); // Criar um novo usuário
    Route::get('{user}', [UserController::class, 'show']); // Exibir um usuário específico
    Route::put('{user}', [UserController::class, 'update']); // Atualizar um usuário
    Route::delete('{user}', [UserController::class, 'destroy']); // Excluir um usuário
});

// Rotas para agendamentos (bookings)
Route::middleware('auth:sanctum')->prefix('bookings')->group(function () {
    Route::get('/my-bookings', [BookingController::class, 'myBookings']); // Listar os próprios agendamentos
    Route::post('/', [BookingController::class, 'store']); // Criar um novo agendamento
    Route::delete('{booking}', [BookingController::class, 'destroy']); // Excluir um agendamento

    // Rota para amistosos (somente 1ª e 3ª semana do mês)
    Route::post('/friendly-match', [BookingController::class, 'storeFriendlyMatch']);
});

// Rotas para a gestão (Management) - Apenas gestores podem acessar
Route::middleware(['auth:sanctum', 'role:gestor'])->prefix('management')->group(function () {
    Route::post('/suspensions', [ManagementController::class, 'createSuspension']); // Criar período de suspensão
    Route::delete('/suspensions/{id}', [ManagementController::class, 'deleteSuspension']); // Remover suspensão
    Route::get('/suspensions', [ManagementController::class, 'listSuspensions']); // Listar suspensões
});
