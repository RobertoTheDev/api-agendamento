<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ManagementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rotas de autenticação
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);

    // Rotas protegidas por autenticação
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    
    // Rotas para usuários (apenas gestores e admins)
    Route::middleware('\App\Http\Middleware\CheckRole:gestor,admin')->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('{user}', [UserController::class, 'show']);
        Route::put('{user}', [UserController::class, 'update']);
        Route::delete('{user}', [UserController::class, 'destroy']);
    });

    // Rotas para agendamentos
    Route::prefix('bookings')->group(function () {
        // Rotas acessíveis por todos os usuários autenticados
        Route::get('my-bookings', [BookingController::class, 'myBookings']);
        Route::post('/', [BookingController::class, 'store']);
        Route::delete('{booking}', [BookingController::class, 'destroy']);
        Route::post('friendly-match', [BookingController::class, 'storeFriendlyMatch']);
        
        // Rotas apenas para gestores
        Route::middleware('\App\Http\Middleware\CheckRole:gestor,admin')->group(function () {
            Route::get('/', [BookingController::class, 'index']);
            Route::get('user/{userId}', [BookingController::class, 'getUserBookings']);
        });
    });

    // Rotas para gestão (apenas gestores e admins)
    Route::middleware('\App\Http\Middleware\CheckRole:gestor,admin')->prefix('management')->group(function () {
        Route::get('suspensions', [ManagementController::class, 'listSuspensions']);
        Route::post('suspensions', [ManagementController::class, 'createSuspension']);
        Route::get('suspensions/{id}', [ManagementController::class, 'showSuspension']);
        Route::put('suspensions/{id}', [ManagementController::class, 'updateSuspension']);
        Route::delete('suspensions/{id}', [ManagementController::class, 'deleteSuspension']);
    });
});

// Rota de fallback para APIs não encontradas
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint não encontrado. Verifique a URL e o método HTTP.',
    ], 404);
});