<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function createBooking(User $user, array $data): Booking
    {
        $this->validateBookingData($data);
        
        return $user->bookings()->create([
            'location' => $data['location'],
            'lesson_period' => $data['lesson_period'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'booking_type' => Booking::TYPE_REGULAR,
            'status' => Booking::STATUS_SCHEDULED,
            'is_evaluation_period' => $data['is_evaluation_period'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function createFriendlyMatch(User $user, array $data): Booking
    {
        $this->validateFriendlyMatchData($data);
        
        return $user->bookings()->create([
            'location' => $data['location'],
            'lesson_period' => $data['lesson_period'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'booking_type' => Booking::TYPE_FRIENDLY_MATCH,
            'status' => Booking::STATUS_SCHEDULED,
            'is_evaluation_period' => $data['is_evaluation_period'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function getUserBookings(User $user): Collection
    {
        return $user->bookings()
                   ->active()
                   ->with('user')
                   ->orderBy('start_time', 'asc')
                   ->get();
    }

    public function cancelBooking(Booking $booking): bool
    {
        if (!$booking->canBeCancelled()) {
            return false;
        }

        $booking->update(['status' => Booking::STATUS_CANCELLED]);
        return true;
    }

    public function getUpcomingBookings(): Collection
    {
        return Booking::active()
                     ->where('start_time', '>', now())
                     ->with('user')
                     ->orderBy('start_time', 'asc')
                     ->get();
    }

    public function getBookingsByLocation(string $location): Collection
    {
        return Booking::where('location', $location)
                     ->active()
                     ->with('user')
                     ->orderBy('start_time', 'asc')
                     ->get();
    }

    private function validateBookingData(array $data): void
    {
        $startTime = Carbon::parse($data['start_time']);

        // Verificar se não é fim de semana
        if (!Booking::isNotWeekend($startTime)) {
            throw ValidationException::withMessages([
                'start_time' => ['Agendamentos não são permitidos aos finais de semana.'],
            ]);
        }

        // Verificar se é no futuro
        if ($startTime->isPast()) {
            throw ValidationException::withMessages([
                'start_time' => ['Não é possível agendar no passado.'],
            ]);
        }

        // Verificar período válido
        if (!Booking::isValidLessonPeriod($data['lesson_period'])) {
            throw ValidationException::withMessages([
                'lesson_period' => ['O período deve estar entre 1 e 9.'],
            ]);
        }

        // Verificar disponibilidade do local
        if (!Booking::isLocationAvailable(
            $data['location'],
            $data['start_time'],
            $data['lesson_period'],
            $data['is_evaluation_period'] ?? false
        )) {
            throw ValidationException::withMessages([
                'location' => ['Local não disponível para esta data e período.'],
            ]);
        }
    }

    private function validateFriendlyMatchData(array $data): void
    {
        // Validações básicas do agendamento
        $this->validateBookingData($data);

        $startTime = Carbon::parse($data['start_time']);

        // Verificar se está na 1ª ou 3ª semana do mês
        if (!Booking::isValidFriendlyMatchWeek($startTime)) {
            throw ValidationException::withMessages([
                'start_time' => ['Amistosos só podem ser agendados na 1ª ou 3ª semana do mês.'],
            ]);
        }
    }
}