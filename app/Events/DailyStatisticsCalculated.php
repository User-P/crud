<?php

namespace App\Events;

use App\Models\DailyStatistic;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento disparado cuando las estadísticas diarias son calculadas
 * 
 * Permite a otros componentes del sistema reaccionar al cálculo
 * de estadísticas, como enviar notificaciones o actualizar caches.
 */
class DailyStatisticsCalculated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Las estadísticas calculadas
     *
     * @var DailyStatistic
     */
    public DailyStatistic $statistics;

    /**
     * Create a new event instance.
     *
     * @param DailyStatistic $statistics
     */
    public function __construct(DailyStatistic $statistics)
    {
        $this->statistics = $statistics;
    }
}
