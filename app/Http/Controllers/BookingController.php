<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Models\Suspension;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Listar todos os agendamentos do usuário autenticado.
     */
    public function index()
    {
        $user = auth()->user();
        return response()->json($user->bookings, 200);
    }

    /**
     * Listar todos os agendamentos de um usuário específico.
     */
    public function getUserBookings($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado.'], 404);
        }

        return response()->json($user->bookings, 200);
    }

    /**
     * Criar um novo agendamento.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'lesson_period' => 'required|integer|between:1,9',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_evaluation_period' => 'required|boolean',
        ]);

        $user_id = auth()->id();
        $startDate = Carbon::parse($validated['start_time']);

        // Verificar se já existe um agendamento no mesmo local, período e data
        $exists = Booking::where('location', $validated['location'])
            ->where('lesson_period', $validated['lesson_period'])
            ->whereDate('start_time', $startDate->toDateString())
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Já existe um agendamento para este local e período.'], 409);
        }

        // Verificar se há suspensão ativa
        if ($this->isSuspended($validated['location'], $validated['start_time'], $validated['is_evaluation_period'])) {
            return response()->json(['error' => 'Este espaço está suspenso para agendamento no momento.'], 403);
        }

        // Criar o agendamento
        $booking = Booking::create([
            'user_id' => $user_id,
            ...$validated
        ]);

        return response()->json([
            'message' => 'Agendamento criado com sucesso!',
            'booking' => $booking
        ], 201);
    }

    /**
     * Criar um agendamento para amistoso (somente na 1ª e 3ª semana do mês).
     */
    public function storeFriendlyMatch(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'lesson_period' => 'required|integer|between:1,9',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_evaluation_period' => 'required|boolean',
        ]);

        $user_id = auth()->id();
        $startDate = Carbon::parse($validated['start_time']);
        $weekOfMonth = ceil($startDate->day / 7);

        // Verificar se o amistoso está na 1ª ou 3ª semana
        if (!in_array($weekOfMonth, [1, 3])) {
            return response()->json(['error' => 'Amistosos só podem ser agendados na 1ª ou 3ª semana do mês.'], 403);
        }

        // Verificar suspensão ativa
        if ($this->isSuspended($validated['location'], $validated['start_time'], $validated['is_evaluation_period'])) {
            return response()->json(['error' => 'Este espaço está suspenso para agendamento no momento.'], 403);
        }

        // Criar o agendamento de amistoso
        $booking = Booking::create([
            'user_id' => $user_id,
            ...$validated
        ]);

        return response()->json([
            'message' => 'Amistoso agendado com sucesso!',
            'booking' => $booking
        ], 201);
    }

    /**
     * Excluir um agendamento.
     */
    public function destroy($id)
    {
        $booking = Booking::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$booking) {
            return response()->json(['error' => 'Agendamento não encontrado ou não pertence a este usuário.'], 404);
        }

        $booking->delete();
        return response()->json(['message' => 'Agendamento excluído com sucesso.'], 200);
    }

    /**
     * Verifica se um local está suspenso na data escolhida e se é período de avaliação.
     */
    private function isSuspended($location, $date, $is_evaluation_period)
    {
        $query = Suspension::where('location', $location)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);

        if ($is_evaluation_period) {
            $query->where('is_evaluation_period', true);
        }

        return $query->exists();
    }
}
