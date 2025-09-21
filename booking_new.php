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
    
    public function myBookings(Request $request): JsonResponse
    {
        $user = $request->user();
        $bookings = Booking::where('user_id', $user->id)
                          ->orderBy('start_time', 'desc')
                          ->paginate(20);
        return response()->json($bookings);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string'
        ]);

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'location' => $validated['location'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'scheduled'
        ]);

        return response()->json([
            'message' => 'Agendamento criado com sucesso.',
            'booking' => $booking->load('user')
        ], 201);
    }

    public function destroy(Booking $booking, Request $request): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id && !$request->user()->isGestor() && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $booking->delete();
        return response()->json(['message' => 'Agendamento cancelado com sucesso.']);
    }

    public function index(): JsonResponse
    {
        $bookings = Booking::with('user')->orderBy('start_time', 'desc')->paginate(20);
        return response()->json($bookings);
    }
}
