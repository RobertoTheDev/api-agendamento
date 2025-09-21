<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Booking;
use App\Models\Suspension;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Criar usuários padrão
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@booking.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '(11) 99999-0001',
        ]);

        $gestor = User::create([
            'name' => 'Gestor Principal',
            'email' => 'gestor@booking.com',
            'password' => Hash::make('password123'),
            'role' => 'gestor',
            'phone' => '(11) 99999-0002',
        ]);

        $professor1 = User::create([
            'name' => 'Professor João',
            'email' => 'professor@booking.com',
            'password' => Hash::make('password123'),
            'role' => 'professor',
            'phone' => '(11) 99999-0003',
        ]);

        $this->command->info('Database seeded successfully!');
    }
}
