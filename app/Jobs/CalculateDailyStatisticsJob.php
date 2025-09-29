<?php

namespace App\Jobs;

use App\Events\DailyStatisticsCalculated;
use App\Models\Country;
use App\Models\DailyStatistic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable as BusQueueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Job para calcular estadísticas diarias de forma asíncrona
 *
 * Se ejecuta automáticamente cada noche para pre-calcular estadísticas
 * y optimizar el rendimiento de consultas frecuentes.
 */
class CalculateDailyStatisticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, BusQueueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job may run.
     *
     * @var int
     */
    public $timeout = 300; // 5 minutos

    /**
     * Fecha para la cual calcular estadísticas
     *
     * @var Carbon
     */
    protected Carbon $date;

    /**
     * Create a new job instance.
     *
     * @param Carbon|null $date Fecha específica o ayer por defecto
     */
    public function __construct(?Carbon $date = null)
    {
        $this->date = ($date ?? Carbon::yesterday())->copy()->startOfDay();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            Log::info("Iniciando cálculo de estadísticas diarias para: {$this->date->toDateString()}");

            DB::beginTransaction();

            $statistics = $this->calculateStatistics();

            // Crear o actualizar estadísticas para la fecha
            $dailyStats = DailyStatistic::updateOrCreate(
                ['date' => $this->date->toDateString()],
                $statistics
            );

            DB::commit();

            // Disparar evento de estadísticas calculadas
            event(new DailyStatisticsCalculated($dailyStats));

            Log::info("Estadísticas calculadas exitosamente para: {$this->date->toDateString()}", [
                'total_users' => $statistics['total_users'],
                'active_users' => $statistics['active_users'],
                'new_registrations' => $statistics['new_registrations'],
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error calculando estadísticas diarias', [
                'date' => $this->date->toDateString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Calculate all daily statistics
     *
     * @return array
     */
    protected function calculateStatistics(): array
    {
        // Usuarios totales hasta la fecha
        $startOfDay = $this->date->copy()->startOfDay();
        $endOfDay = $this->date->copy()->endOfDay();
        $sevenDaysAgo = $startOfDay->copy()->subDays(7);

        $totalUsers = User::where('created_at', '<=', $endOfDay)->count();

        // Usuarios activos (con tokens activos en los últimos 7 días)
        $activeUsers = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where(function ($query) use ($endOfDay) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $endOfDay);
            })
            ->where(function ($query) use ($sevenDaysAgo, $endOfDay) {
                $query->whereBetween('last_used_at', [$sevenDaysAgo, $endOfDay])
                    ->orWhere(function ($inner) use ($sevenDaysAgo, $endOfDay) {
                        $inner->whereNull('last_used_at')
                            ->whereBetween('created_at', [$sevenDaysAgo, $endOfDay]);
                    });
            })
            ->distinct()
            ->count('tokenable_id');

        // Nuevos registros del día
        $newRegistrations = User::whereBetween('created_at', [$startOfDay, $endOfDay])->count();

        // Total de países
        $totalCountries = Country::count();

        // Usuarios administradores
        $adminUsers = User::where('role', 'admin')
            ->where('created_at', '<=', $endOfDay)
            ->count();

        // Tasa de verificación
        $verifiedUsers = User::whereNotNull('email_verified_at')
            ->where('created_at', '<=', $endOfDay)
            ->count();

        $verificationRate = $totalUsers > 0 ? ($verifiedUsers / $totalUsers) * 100 : 0;

        // Usuarios por región (basado en países)
        $usersByRegion = $this->calculateUsersByRegion();

        // Registros por hora del día
        $registrationsByHour = $this->calculateRegistrationsByHour();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'new_registrations' => $newRegistrations,
            'total_countries' => $totalCountries,
            'admin_users' => $adminUsers,
            'verification_rate' => round($verificationRate, 2),
            'users_by_region' => $usersByRegion,
            'registrations_by_hour' => $registrationsByHour,
        ];
    }

    /**
     * Calculate users distribution by region
     *
     * @return array
     */
    protected function calculateUsersByRegion(): array
    {
        return Country::selectRaw('region, COUNT(*) as count')
            ->whereNotNull('region')
            ->groupBy('region')
            ->pluck('count', 'region')
            ->toArray();
    }

    /**
     * Calculate registrations by hour for the specific date
     *
     * @return array
     */
    protected function calculateRegistrationsByHour(): array
    {
        // Usar función compatible con SQLite
        $startOfDay = $this->date->copy()->startOfDay();
        $endOfDay = $this->date->copy()->endOfDay();
        $hourExpression = $this->hourGroupingExpression();

        $registrations = User::selectRaw("{$hourExpression} as hour, COUNT(*) as count")
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->groupByRaw($hourExpression)
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $registrations = collect($registrations)
            ->mapWithKeys(static fn ($count, $hour) => [(int) $hour => (int) $count])
            ->toArray();

        // Rellenar todas las horas (0-23) con 0 si no hay datos
        $result = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $result[$hour] = $registrations[$hour] ?? 0;
        }

        return $result;
    }

    /**
     * Handle a job failure.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception): void
    {
        Log::error('Job de estadísticas diarias falló definitivamente', [
            'date' => $this->date->toDateString(),
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Aquí podrías enviar notificaciones a administradores
    }

    /**
     * Obtener la expresión SQL correcta para agrupar por hora según el driver.
     */
    protected function hourGroupingExpression(): string
    {
        return match (DB::getDriverName()) {
            'mysql' => 'HOUR(created_at)',
            'pgsql' => 'EXTRACT(HOUR FROM created_at)::int',
            'sqlsrv' => 'DATEPART(hour, created_at)',
            default => "CAST(strftime('%H', created_at) AS INTEGER)",
        };
    }
}
