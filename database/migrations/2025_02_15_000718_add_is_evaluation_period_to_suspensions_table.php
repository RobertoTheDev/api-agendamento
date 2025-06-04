<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsEvaluationPeriodToSuspensionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suspensions', function (Blueprint $table) {
            $table->boolean('is_evaluation_period')->default(false); // Adiciona o campo para definir se é um período de avaliação
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suspensions', function (Blueprint $table) {
            $table->dropColumn('is_evaluation_period'); // Remove o campo, caso a migração seja revertida
        });
    }
}
