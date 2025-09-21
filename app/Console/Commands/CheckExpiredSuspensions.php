<?php

// app/Console/Commands/CheckExpiredSuspensions.php
namespace App\Console\Commands;

use App\Models\Suspension;
use App\Models\User;
use Illuminate\Console\Command;

class CheckExpiredSuspensions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suspensions:check-expired 
                            {--dry-run : Executar sem fazer alterações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and deactivate expired suspensions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔍 Verificando suspensões expiradas...');
        
        $expiredSuspensions = Suspension::where('is_active', true)
                                       ->where('end_date', '<', now())
                                       ->get();

        if ($expiredSuspensions->isEmpty()) {
            $this->info('✅ Nenhuma suspensão expirada encontrada.');
            return Command::SUCCESS;
        }

        $this->info("📋 Encontradas {$expiredSuspensions->count()} suspensões expiradas:");

        $processedUsers = [];
        
        foreach ($expiredSuspensions as $suspension) {
            $this->line("  • ID: {$suspension->id} | Local: {$suspension->location} | Usuário: " . 
                       ($suspension->user ? $suspension->user->name : 'N/A'));
            
            if (!$dryRun) {
                $suspension->update(['is_active' => false]);
                
                // Se tem usuário associado, verificar outras suspensões
                if ($suspension->user_id && !in_array($suspension->user_id, $processedUsers)) {
                    $hasActiveSuspensions = Suspension::where('user_id', $suspension->user_id)
                                                     ->where('id', '!=', $suspension->id)
                                                     ->where('is_active', true)
                                                     ->where('start_date', '<=', now())
                                                     ->where('end_date', '>=', now())
                                                     ->exists();
                    
                    if (!$hasActiveSuspensions) {
                        User::find($suspension->user_id)->update(['is_suspended' => false]);
                        $this->line("    ↳ Usuário {$suspension->user->name} reativado");
                    }
                    
                    $processedUsers[] = $suspension->user_id;
                }
            }
        }

        if ($dryRun) {
            $this->warn('🔄 Modo dry-run ativo. Nenhuma alteração foi feita.');
            $this->info('Execute sem --dry-run para aplicar as alterações.');
        } else {
            $this->info("✅ Processadas {$expiredSuspensions->count()} suspensões expiradas.");
        }
        
        return Command::SUCCESS;
    }
}

// app/Console/Commands/BookingStats.php
namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class BookingStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:stats 
                            {--period=month : Período (week, month, year)}
                            {--location= : Filtrar por local}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show booking statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $period = $this->option('period');
        $location = $this->option('location');
        
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $this->info("📊 Estatísticas de Agendamentos - " . ucfirst($period));
        $this->info("📅 Período: {$startDate->format('d/m/Y')} até " . now()->format('d/m/Y'));
        
        if ($location) {
            $this->info("📍 Local: {$location}");
        }
        
        $this->line('');

        // Query base
        $query = Booking::where('created_at', '>=', $startDate);
        
        if ($location) {
            $query->where('location', $location);
        }

        // Estatísticas gerais
        $totalBookings = $query->count();
        $activeBookings = $query->where('status', Booking::STATUS_SCHEDULED)->count();
        $cancelledBookings = $query->where('status', Booking::STATUS_CANCELLED)->count();
        $friendlyMatches = $query->where('booking_type', Booking::TYPE_FRIENDLY_MATCH)->count();

        $this->table([
            'Métrica',
            'Valor',
        ], [
            ['Total de Agendamentos', $totalBookings],
            ['Agendamentos Ativos', $activeBookings],
            ['Agendamentos Cancelados', $cancelledBookings],
            ['Amistosos', $friendlyMatches],
            ['Taxa de Cancelamento', $totalBookings > 0 ? round(($cancelledBookings / $totalBookings) * 100, 2) . '%' : '0%'],
        ]);

        // Top usuários
        $topUsers = User::withCount(['bookings' => function ($query) use ($startDate, $location) {
            $query->where('created_at', '>=', $startDate);
            if ($location) {
                $query->where('location', $location);
            }
        }])
        ->having('bookings_count', '>', 0)
        ->orderBy('bookings_count', 'desc')
        ->limit(5)
        ->get();

        if ($topUsers->isNotEmpty()) {
            $this->line('');
            $this->info('🏆 Top 5 Usuários:');
            
            $userData = $topUsers->map(function ($user) {
                return [
                    $user->name,
                    $user->role,
                    $user->bookings_count,
                ];
            })->toArray();
            
            $this->table(['Nome', 'Tipo', 'Agendamentos'], $userData);
        }

        return Command::SUCCESS;
    }
}

// app/Console/Kernel.php - ATUALIZAÇÃO
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Verificar suspensões expiradas diariamente às 00:01
        $schedule->command('suspensions:check-expired')
                 ->dailyAt('00:01')
                 ->withoutOverlapping()
                 ->onSuccess(function () {
                     \Log::info('Comando suspensions:check-expired executado com sucesso.');
                 })
                 ->onFailure(function () {
                     \Log::error('Erro ao executar comando suspensions:check-expired.');
                 });

        // Gerar estatísticas semanais às segundas-feiras
        $schedule->command('bookings:stats --period=week')
                 ->weeklyOn(1, '08:00')
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}