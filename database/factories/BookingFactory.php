<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $endTime = Carbon::instance($startTime)->addHour();
        
        return [
            'user_id' => User::factory(),
            'location' => $this->faker->randomElement(['Quadra 1', 'Quadra 2', 'Laboratório A', 'Laboratório B']),
            'lesson_period' => $this->faker->numberBetween(1, 9),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'booking_type' => 'regular',
            'status' => 'scheduled',
            'is_evaluation_period' => false,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function friendlyMatch(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_type' => 'friendly_match',
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
