<?php

// ARQUIVO: database/migrations/2025_02_13_191941_create_bookings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('location');
            $table->integer('lesson_period');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('booking_type', ['regular', 'friendly_match'])->default('regular');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->boolean('is_evaluation_period')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['user_id', 'status', 'start_time']);
            $table->index(['location', 'lesson_period', 'start_time']);
            $table->index(['booking_type', 'start_time']);
            $table->index(['status', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
