<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvaluationPeriodToSuspensionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suspensions', function (Blueprint $table) {
            $table->boolean('is_evaluation_period')->default(false); // Campo para período de avaliação
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suspensions', function (Blueprint $table) {
            $table->dropColumn('is_evaluation_period');
        });
    }
}
