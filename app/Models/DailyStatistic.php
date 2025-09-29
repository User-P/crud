<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Daily Statistics Model
 *
 * Almacena estadísticas diarias calculadas de forma asíncrona
 * para optimizar el rendimiento de consultas frecuentes.
 */
class DailyStatistic extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'total_users',
        'active_users',
        'new_registrations',
        'total_countries',
        'admin_users',
        'verification_rate',
        'users_by_region',
        'registrations_by_hour',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'users_by_region' => 'array',
        'registrations_by_hour' => 'array',
        'verification_rate' => 'decimal:2',
    ];

    /**
     * Scope para obtener estadísticas de los últimos N días
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLastDays($query, int $days = 30)
    {
        return $query->where('date', '>=', Carbon::now()->subDays($days))
            ->orderBy('date', 'desc');
    }

    /**
     * Scope para estadísticas del mes actual
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year);
    }

    /**
     * Obtener la tendencia de crecimiento
     *
     * @return array
     */
    public function getTrendAttribute(): array
    {
        $previous = static::where('date', '<', $this->date)
            ->orderBy('date', 'desc')
            ->first();

        if (!$previous) {
            return ['growth_rate' => 0, 'comparison' => 'No hay datos anteriores'];
        }

        $growthRate = $previous->total_users > 0
            ? (($this->total_users - $previous->total_users) / $previous->total_users) * 100
            : 100;

        return [
            'growth_rate' => round($growthRate, 2),
            'comparison' => $growthRate > 0 ? 'positive' : ($growthRate < 0 ? 'negative' : 'stable'),
            'difference' => $this->total_users - $previous->total_users,
        ];
    }
}
