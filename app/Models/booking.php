<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Suspension;

class Booking extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',    // ID do usuário que fez o agendamento
        'location',   // Local reservado (quadra, laboratório, etc.)
        'lesson_period', // Período da aula (de 1 a 9)
        'start_time', // Hora de início do agendamento
        'end_time',   // Hora de término do agendamento
        'is_evaluation_period', // Se o agendamento é durante o período de avaliação
    ];

    /**
     * Definir o relacionamento inverso com o modelo User.
     * Um agendamento pertence a um usuário (professor).
     */
    public function user()
    {
        return $this->belongsTo(User::class); // Relacionamento inverso com a tabela de usuários
    }

    /**
     * Verifica se o local está disponível para agendamento.
     * Verifica se existe uma suspensão para o local nas datas do agendamento,
     * incluindo a verificação para períodos de avaliação.
     *
     * @param string $location
     * @param string $date
     * @param bool $isEvaluationPeriod
     * @return bool
     */
    public static function isLocationAvailable($location, $date, $isEvaluationPeriod = false)
    {
        // Verifica se o local está suspenso na data do agendamento
        $suspensionQuery = Suspension::where('location', $location)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);

        // Se for período de avaliação, verifica se a suspensão se aplica ao período de avaliação
        if ($isEvaluationPeriod) {
            $suspensionQuery->where('is_evaluation_period', true);
        }

        $suspension = $suspensionQuery->first();

        return $suspension ? false : true;
    }

    /**
     * Verifica se o agendamento está dentro do período válido (de 1 a 9).
     * Isso garante que o agendamento é feito dentro do intervalo correto.
     *
     * @param int $period
     * @return bool
     */
    public static function isValidLessonPeriod($period)
    {
        return $period >= 1 && $period <= 9;
    }
}
