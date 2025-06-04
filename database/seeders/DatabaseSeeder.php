<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criando um gestor
        User::factory()->create([
            'name' => 'Gestor Principal',
            'email' => 'gestor@example.com',
            'password' => Hash::make('password'),
            'role' => 'gestor',
        ]);

        // Criando alguns professores
        User::factory(5)->create([
            'role' => 'professor',
        ]);
    }
}
