<?php

namespace App\Listeners;

use App\Events\DailyStatisticsCalculated;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener para notificar a administradores cuando se calculan estadísticas
 * 
 * Implementa ShouldQueue para ejecutarse de forma asíncrona
 */
class NotifyAdminsStatisticsCalculated implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the listener may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param DailyStatisticsCalculated $event
     * @return void
     */
    public function handle(DailyStatisticsCalculated $event): void
    {
        $statistics = $event->statistics;

        // Log de la actividad
        Log::info('Estadísticas diarias calculadas', [
            'date' => $statistics->date->toDateString(),
            'total_users' => $statistics->total_users,
            'new_registrations' => $statistics->new_registrations,
            'active_users' => $statistics->active_users,
        ]);

        // Aquí podrías implementar notificaciones reales:
        // - Enviar emails a administradores
        // - Crear notificaciones en la aplicación
        // - Enviar a Slack/Discord
        // - Actualizar métricas en servicios externos

        $this->notifyAdministrators($statistics);
    }

    /**
     * Notificar a administradores (implementación ejemplo)
     *
     * @param \App\Models\DailyStatistic $statistics
     * @return void
     */
    protected function notifyAdministrators($statistics): void
    {
        $adminCount = User::where('role', 'admin')->count();

        Log::info("Notificando estadísticas a {$adminCount} administradores", [
            'date' => $statistics->date->toDateString(),
            'growth_rate' => $statistics->trend['growth_rate'] ?? 0,
        ]);

        // Ejemplo: Aquí implementarías el envío real de notificaciones
        // Mail::to($admins)->send(new DailyStatisticsReport($statistics));
    }

    /**
     * Handle a job failure.
     *
     * @param DailyStatisticsCalculated $event
     * @param \Exception $exception
     * @return void
     */
    public function failed(DailyStatisticsCalculated $event, $exception): void
    {
        Log::error('Falló la notificación de estadísticas diarias', [
            'date' => $event->statistics->date->toDateString(),
            'error' => $exception->getMessage(),
        ]);
    }
}
