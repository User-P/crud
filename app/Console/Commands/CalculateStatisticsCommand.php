<?php

namespace App\Console\Commands;

use App\Jobs\CalculateDailyStatisticsJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Comando para calcular estadísticas diarias
 * 
 * Puede ejecutarse manualmente o programarse con cron
 */
class CalculateStatisticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:calculate 
                           {--date= : Fecha específica (Y-m-d), por defecto ayer}
                           {--queue : Ejecutar en cola en lugar de inmediatamente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calcular estadísticas diarias de usuarios y actividad';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $dateOption = $this->option('date');
        $useQueue = $this->option('queue');

        // Parsear fecha o usar ayer por defecto
        if ($dateOption) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $dateOption);
            } catch (\Exception $e) {
                $this->error('Formato de fecha inválido. Use Y-m-d (ej: 2025-09-28)');
                return Command::FAILURE;
            }
        } else {
            $date = Carbon::yesterday();
        }

        $this->info("📊 Calculando estadísticas para: {$date->toDateString()}");

        if ($useQueue) {
            // Despachar a cola
            CalculateDailyStatisticsJob::dispatch($date);
            $this->info('✅ Job despachado a la cola exitosamente');
        } else {
            // Ejecutar inmediatamente
            $this->info('⚡ Ejecutando cálculo inmediato...');
            
            try {
                $job = new CalculateDailyStatisticsJob($date);
                $job->handle();
                $this->info('✅ Estadísticas calculadas exitosamente');
            } catch (\Exception $e) {
                $this->error('❌ Error calculando estadísticas: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
