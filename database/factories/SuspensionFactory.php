<?php

namespace Database\Factories;

use App\Models\Suspension;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuspensionFactory extends Factory
{
    protected $model = Suspension::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-5 days', '+5 days');
        $endDate = $this->faker->dateTimeBetween($startDate, '+15 days');
        
        return [
            'user_id' => User::factory(),
            'location' => $this->faker->randomElement(['Quadra 1', 'Quadra 2', 'Laboratório A']),
            'reason' => $this->faker->sentence(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
            'is_evaluation_period' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDay(),
        ]);
    }
}
