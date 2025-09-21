<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    const TYPE_REGULAR = 'regular';
    const TYPE_FRIENDLY_MATCH = 'friendly_match';

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'location',
        'lesson_period',
        'start_time',
        'end_time',
        'booking_type',
        'status',
        'is_evaluation_period',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_evaluation_period' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeFriendlyMatches($query)
    {
        return $query->where('booking_type', self::TYPE_FRIENDLY_MATCH);
    }

    public function scopeRegular($query)
    {
        return $query->where('booking_type', self::TYPE_REGULAR);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeCancelled(): bool
    {
        return $this->isScheduled() && $this->start_time->isFuture();
    }

    public function isFriendlyMatch(): bool
    {
        return $this->booking_type === self::TYPE_FRIENDLY_MATCH;
    }

    /**
     * Verifica se o local está disponível para agendamento.
     */
    public static function isLocationAvailable($location, $date, $lessonPeriod, $isEvaluationPeriod = false): bool
    {
        // Verifica se já existe agendamento no mesmo local, período e data
        $existingBooking = static::where('location', $location)
            ->where('lesson_period', $lessonPeriod)
            ->whereDate('start_time', Carbon::parse($date)->toDateString())
            ->where('status', '!=', self::STATUS_CANCELLED)
            ->exists();

        if ($existingBooking) {
            return false;
        }

        // Verifica se o local está suspenso
        $suspensionQuery = Suspension::where('location', $location)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('is_active', true);

        if ($isEvaluationPeriod) {
            $suspensionQuery->where('is_evaluation_period', true);
        }

        return !$suspensionQuery->exists();
    }

    /**
     * Verifica se o período de aula é válido (de 1 a 9).
     */
    public static function isValidLessonPeriod($period): bool
    {
        return is_numeric($period) && $period >= 1 && $period <= 9;
    }

    /**
     * Verifica se a data está na 1ª ou 3ª semana do mês (para amistosos).
     */
    public static function isValidFriendlyMatchWeek($date): bool
    {
        $carbon = Carbon::parse($date);
        $weekOfMonth = ceil($carbon->day / 7);
        
        return in_array($weekOfMonth, [1, 3]);
    }

    /**
     * Verifica se a data não é fim de semana.
     */
    public static function isNotWeekend($date): bool
    {
        $carbon = Carbon::parse($date);
        return !$carbon->isWeekend();
    }
}