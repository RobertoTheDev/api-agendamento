<?php
namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Suspension;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private User $professor;
    private User $gestor;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->professor = User::factory()->create(['role' => 'professor']);
        $this->gestor = User::factory()->create(['role' => 'gestor']);
    }

    public function test_professor_can_create_booking(): void
    {
        $futureDate = Carbon::now()->addDays(5)->setHour(14)->setMinute(0);
        
        $response = $this->actingAs($this->professor, 'sanctum')
                        ->postJson('/api/bookings', [
                            'location' => 'Quadra 1',
                            'lesson_period' => 3,
                            'start_time' => $futureDate->format('Y-m-d H:i:s'),
                            'end_time' => $futureDate->copy()->addHour()->format('Y-m-d H:i:s'),
                            'is_evaluation_period' => false,
                            'notes' => 'Aula de tênis',
                        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'booking' => ['id', 'location', 'lesson_period', 'booking_type'],
                ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->professor->id,
            'location' => 'Quadra 1',
            'booking_type' => 'regular',
            'status' => 'scheduled',
        ]);
    }

    public function test_cannot_book_on_weekend(): void
    {
        $weekend = Carbon::now()->next(Carbon::SATURDAY)->setHour(14);
        
        $response = $this->actingAs($this->professor, 'sanctum')
                        ->postJson('/api/bookings', [
                            'location' => 'Quadra 1',
                            'lesson_period' => 3,
                            'start_time' => $weekend->format('Y-m-d H:i:s'),
                            'end_time' => $weekend->copy()->addHour()->format('Y-m-d H:i:s'),
                            'is_evaluation_period' => false,
                        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_book_same_location_and_period(): void
    {
        $futureDate = Carbon::now()->addDays(5)->setHour(14);
        
        // Criar primeiro agendamento
        Booking::factory()->create([
            'user_id' => $this->professor->id,
            'location' => 'Quadra 1',
            'lesson_period' => 3,
            'start_time' => $futureDate,
            'status' => 'scheduled',
        ]);

        // Tentar criar segundo agendamento no mesmo local e período
        $response = $this->actingAs($this->professor, 'sanctum')
                        ->postJson('/api/bookings', [
                            'location' => 'Quadra 1',
                            'lesson_period' => 3,
                            'start_time' => $futureDate->format('Y-m-d H:i:s'),
                            'end_time' => $futureDate->copy()->addHour()->format('Y-m-d H:i:s'),
                            'is_evaluation_period' => false,
                        ]);

        $response->assertStatus(422);
    }

    public function test_friendly_match_only_in_allowed_weeks(): void
    {
        // Encontrar uma data na 2ª semana do mês (não permitida)
        $secondWeekDate = Carbon::now()->startOfMonth()->addWeeks(1)->addDays(2)->setHour(14);

        $response = $this->actingAs($this->professor, 'sanctum')
                        ->postJson('/api/bookings/friendly-match', [
                            'location' => 'Quadra 1',
                            'lesson_period' => 3,
                            'start_time' => $secondWeekDate->format('Y-m-d H:i:s'),
                            'end_time' => $secondWeekDate->copy()->addHour()->format('Y-m-d H:i:s'),
                            'is_evaluation_period' => false,
                        ]);

        $response->assertStatus(422);
    }

    public function test_professor_can_view_own_bookings(): void
    {
        Booking::factory()->count(3)->create(['user_id' => $this->professor->id]);

        $response = $this->actingAs($this->professor, 'sanctum')
                        ->getJson('/api/bookings/my-bookings');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'bookings' => [
                        '*' => ['id', 'location', 'lesson_period', 'status'],
                    ],
                ]);
    }

    public function test_professor_can_cancel_own_booking(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->professor->id,
            'start_time' => Carbon::now()->addDays(5),
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->professor, 'sanctum')
                        ->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_gestor_can_view_all_bookings(): void
    {
        Booking::factory()->count(5)->create();

        $response = $this->actingAs($this->gestor, 'sanctum')
                        ->getJson('/api/bookings');

        $response->assertStatus(200);
    }

    public function test_professor_cannot_view_all_bookings(): void
    {
        $response = $this->actingAs($this->professor, 'sanctum')
                        ->getJson('/api/bookings');

        $response->assertStatus(403);
    }
}