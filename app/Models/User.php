<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Certifique-se de adicionar essa linha!

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    // Adiciona 'role' ao array $fillable
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'role', // Incluindo o campo 'role'
    ];

    // Campos a serem ocultados
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Definições de conversões de tipos de dados
    protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
    ];
}


    // Relacionamento com Booking (agendamentos)
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Validando o valor de 'role' antes de salvar no banco
    public static function booted()
    {
        static::saving(function ($user) {
            // Garantir que o role seja um valor válido
            if (!in_array($user->role, ['professor', 'gestor', 'admin'])) {
                throw new \Exception("Tipo de usuário inválido.");
            }
        });
    }
}
