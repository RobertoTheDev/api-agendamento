<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_GESTOR = 'gestor';
    const ROLE_PROFESSOR = 'professor';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_suspended',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_suspended' => 'boolean',
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isGestor(): bool
    {
        return $this->role === self::ROLE_GESTOR;
    }

    public function isProfessor(): bool
    {
        return $this->role === self::ROLE_PROFESSOR;
    }

    public function hasActiveSuspension(): bool
    {
        return $this->suspensions()
                    ->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->exists();
    }

    public function activeSuspension()
    {
        return $this->suspensions()
                    ->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->first();
    }

    public function suspensions()
    {
        return $this->hasMany(Suspension::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
