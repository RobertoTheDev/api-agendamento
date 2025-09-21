<?php

namespace App\Http\Controllers;


use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Requests\Booking\CreateFriendlyMatchRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/bookings/my-bookings",
     *     tags={"Agendamentos"},
     *     summary="Listar meus agendamentos",
     *     description="Retorna todos os agendamentos do usuário autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de agendamentos",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="bookings",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Booking")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado"
     *     )
     * )
     */
    public function myBookings(Request $request): JsonResponse
    {
        try {
            $bookings = $this->bookingService->getUserBookings($request->user());
            
            return response()->json([
                'bookings' => $bookings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar agendamentos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bookings",
     *     tags={"Agendamentos"},
     *     summary="Criar agendamento",
     *     description="Cria um novo agendamento regular",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"location","lesson_period","start_time","end_time","is_evaluation_period"},
     *             @OA\Property(property="location", type="string", example="Quadra 1"),
     *             @OA\Property(property="lesson_period", type="integer", minimum=1, maximum=9, example=3),
     *             @OA\Property(property="start_time", type="string", format="datetime", example="2024-12-25 14:00:00"),
     *             @OA\Property(property="end_time", type="string", format="datetime", example="2024-12-25 15:00:00"),
     *             @OA\Property(property="is_evaluation_period", type="boolean", example=false),
     *             @OA\Property(property="notes", type="string", example="Aula de tênis")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Agendamento criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Agendamento criado com sucesso!"),
     *             @OA\Property(property="booking", ref="#/components/schemas/Booking")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dados inválidos ou horário não disponível"
     *     )
     * )
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createBooking(
                $request->user(),
                $request->validated()
            );
            
            return response()->json([
                'message' => 'Agendamento criado com sucesso!',
                'booking' => $booking->load('user'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar agendamento.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bookings/friendly-match",
     *     tags={"Agendamentos"},
     *     summary="Criar amistoso",
     *     description="Cria um agendamento para amistoso (apenas 1ª e 3ª semana do mês)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"location","lesson_period","start_time","end_time","is_evaluation_period"},
     *             @OA\Property(property="location", type="string", example="Quadra 1"),
     *             @OA\Property(property="lesson_period", type="integer", minimum=1, maximum=9, example=3),
     *             @OA\Property(property="start_time", type="string", format="datetime", example="2024-12-07 16:00:00"),
     *             @OA\Property(property="end_time", type="string", format="datetime", example="2024-12-07 17:00:00"),
     *             @OA\Property(property="is_evaluation_period", type="boolean", example=false),
     *             @OA\Property(property="notes", type="string", example="Amistoso de sábado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Amistoso agendado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Amistoso agendado com sucesso!"),
     *             @OA\Property(property="booking", ref="#/components/schemas/Booking")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Data inválida - amistosos só na 1ª e 3ª semana"
     *     )
     * )
     */
    public function storeFriendlyMatch(CreateFriendlyMatchRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createFriendlyMatch(
                $request->user(),
                $request->validated()
            );
            
            return response()->json([
                'message' => 'Amistoso agendado com sucesso!',
                'booking' => $booking->load('user'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao agendar amistoso.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/bookings/{booking}",
     *     tags={"Agendamentos"},
     *     summary="Cancelar agendamento",
     *     description="Cancela um agendamento específico",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="booking",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID do agendamento"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Agendamento cancelado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Agendamento cancelado com sucesso.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Sem permissão para cancelar este agendamento"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Não é possível cancelar este agendamento"
     *     )
     * )
     */
    public function destroy(Booking $booking, Request $request): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id && !$request->user()->canManage()) {
            return response()->json([
                'message' => 'Você não tem permissão para cancelar este agendamento.',
            ], 403);
        }

        try {
            if (!$this->bookingService->cancelBooking($booking)) {
                return response()->json([
                    'message' => 'Não é possível cancelar este agendamento.',
                ], 400);
            }

            return response()->json([
                'message' => 'Agendamento cancelado com sucesso.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao cancelar agendamento.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

// Schemas para reutilização
/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="João Silva"),
 *     @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
 *     @OA\Property(property="role", type="string", enum={"professor","gestor","admin"}, example="professor"),
 *     @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
 *     @OA\Property(property="is_suspended", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-01T00:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2024-01-01T00:00:00.000000Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Booking",
 *     type="object",
