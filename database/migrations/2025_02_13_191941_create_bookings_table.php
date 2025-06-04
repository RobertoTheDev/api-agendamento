<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Relacionamento com a tabela users
            $table->string('location'); // Local reservado (ex: quadra, laboratório)
            $table->integer('lesson_period'); // Representa a aula (de 1 a 9)
            $table->timestamp('start_time'); // Hora de início do agendamento
            $table->timestamp('end_time'); // Hora de término do agendamento
            $table->timestamps(); // Data de criação e atualização do agendamento
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
