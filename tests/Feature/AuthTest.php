<?php

// tests/Feature/AuthTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'senha123456',
            'password_confirmation' => 'senha123456',
            'role' => 'professor',
            'phone' => '(11) 99999-9999',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email', 'role'],
                    'redirect_url',
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'joao@example.com',
            'role' => 'professor',
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'professor@example.com',
            'password' => Hash::make('senha123456'),
            'role' => 'professor',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'professor@example.com',
            'password' => 'senha123456',
            'role' => 'professor',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                    'redirect_url',
                ]);
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'suspenso@example.com',
            'password' => Hash::make('senha123456'),
            'role' => 'professor',
            'is_suspended' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'suspenso@example.com',
            'password' => 'senha123456',
            'role' => 'professor',
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_cannot_login_via_api(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('senha123456'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'senha123456',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/auth/logout');

        $response->assertStatus(200);
        $this->assertCount(0, $user->tokens);
    }
}
