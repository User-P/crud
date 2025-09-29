<?php

namespace App\Http\Controllers;

use App\Models\DailyStatistic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Obtener estadísticas generales de usuarios.
     *
     * @return JsonResponse
     */
    public function userStatistics(): JsonResponse
    {
        $this->authorize('viewAny', User::class); // Solo admins

        // OPTIMIZACIÓN: Una sola consulta agregada para obtener múltiples métricas
        $aggregatedStats = User::selectRaw("
            COUNT(*) as total_users,
            COUNT(CASE WHEN role = 'admin' THEN 1 END) as total_admins,
            COUNT(CASE WHEN role = 'user' THEN 1 END) as total_regular_users,
            COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_users,
            COUNT(CASE WHEN email_verified_at IS NULL THEN 1 END) as unverified_users
        ")->first();

        // Consulta separada solo para usuarios recientes (necesaria por el LIMIT y ORDER BY)
        $recentUsers = User::latest()
            ->take(5)
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        // Calcular porcentajes usando los datos ya obtenidos
        $totalUsers = $aggregatedStats->total_users;
        $verifiedUsers = $aggregatedStats->verified_users;
        $totalAdmins = $aggregatedStats->total_admins;

        $stats = [
            // Conteos básicos (desde consulta agregada)
            'total_users' => $totalUsers,
            'users_by_role' => [
                'admin' => $totalAdmins,
                'user' => $aggregatedStats->total_regular_users,
            ],

            // Verificación de email (desde consulta agregada)
            'verified_users' => $verifiedUsers,
            'unverified_users' => $aggregatedStats->unverified_users,

            // Registros por mes (consulta optimizada separada)
            'registrations_by_month' => $this->getRegistrationsByMonthOptimized(),

            // Usuarios recientes (consulta separada necesaria)
            'recent_users' => $recentUsers,

            // Métricas adicionales (calculadas, no consultas)
            'admin_percentage' => $totalUsers > 0 ? round(($totalAdmins / $totalUsers) * 100, 2) : 0,
            'verification_rate' => $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 2) : 0,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Estadísticas de usuarios obtenidas correctamente',
            'data' => $stats,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Obtener estadísticas de actividad (tokens activos).
     *
     * @return JsonResponse
     */
    public function activityStatistics(): JsonResponse
    {
        $this->authorize('viewAny', User::class); // Solo admins

        $stats = [
            'active_tokens' => DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->count(),

            'tokens_by_user' => DB::table('personal_access_tokens')
                ->join('users', 'users.id', '=', 'personal_access_tokens.tokenable_id')
                ->where('personal_access_tokens.tokenable_type', User::class)
                ->selectRaw('users.name, COUNT(*) as token_count')
                ->groupBy('users.id', 'users.name')
                ->orderBy('token_count', 'desc')
                ->limit(10)
                ->get(),

            'recent_token_activity' => DB::table('personal_access_tokens')
                ->join('users', 'users.id', '=', 'personal_access_tokens.tokenable_id')
                ->where('personal_access_tokens.tokenable_type', User::class)
                ->selectRaw('users.name, personal_access_tokens.name as token_name, personal_access_tokens.last_used_at')
                ->whereNotNull('personal_access_tokens.last_used_at')
                ->orderBy('personal_access_tokens.last_used_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Estadísticas de actividad obtenidas correctamente',
            'data' => $stats,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Dashboard con resumen general.
     *
     * @return JsonResponse
     */
    public function dashboard(): JsonResponse
    {
        $this->authorize('viewAny', User::class); // Solo admins

        $dashboard = [
            // Métricas principales
            'main_metrics' => [
                'total_users' => User::count(),
                'total_admins' => User::where('role', 'admin')->count(),
                'verified_users' => User::whereNotNull('email_verified_at')->count(),
                'active_tokens' => DB::table('personal_access_tokens')
                    ->where('tokenable_type', User::class)
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->count(),
            ],

            // Tendencias (comparación con mes anterior)
            'trends' => [
                'new_users_this_month' => User::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'new_users_last_month' => User::whereMonth('created_at', now()->subMonth()->month)
                    ->whereYear('created_at', now()->subMonth()->year)
                    ->count(),
            ],

            // Actividad reciente
            'recent_activity' => [
                'latest_registrations' => User::latest()->take(5)
                    ->get(['name', 'email', 'role', 'created_at']),
                'recent_logins' => DB::table('personal_access_tokens')
                    ->join('users', 'users.id', '=', 'personal_access_tokens.tokenable_id')
                    ->where('personal_access_tokens.tokenable_type', User::class)
                    ->selectRaw('users.name, personal_access_tokens.created_at as login_time')
                    ->orderBy('personal_access_tokens.created_at', 'desc')
                    ->limit(5)
                    ->get(),
            ],
        ];

        // Calcular growth rate
        $thisMonth = $dashboard['trends']['new_users_this_month'];
        $lastMonth = $dashboard['trends']['new_users_last_month'];
        $dashboard['trends']['growth_rate'] = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2)
            : ($thisMonth > 0 ? 100 : 0);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard obtenido correctamente',
            'data' => $dashboard,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Obtener registros por mes (últimos 6 meses) con consulta optimizada.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRegistrationsByMonthOptimized(): array
    {
        // Una sola consulta para obtener todos los conteos por mes (compatible con SQLite)
        $startDate = Carbon::now()->copy()->subMonths(5)->startOfMonth();
        $expression = $this->monthGroupingExpression();

        $results = User::selectRaw("{$expression} as month, COUNT(*) as count")
            ->where('created_at', '>=', $startDate)
            ->groupByRaw($expression)
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Construir array con todos los meses (incluyendo los que tienen 0 registros)
        $months = [];
        $referenceDate = Carbon::now();
        for ($i = 5; $i >= 0; $i--) {
            $date = $referenceDate->copy()->subMonths($i);
            $monthKey = $date->format('Y-m');

            $months[] = [
                'month' => $monthKey,
                'month_name' => $date->translatedFormat('F Y'),
                'count' => $results[$monthKey] ?? 0,
            ];
        }

        return $months;
    }

    /**
     * Obtener la expresión SQL adecuada para agrupar por mes según el driver actual.
     *
     * @return string
     */
    private function monthGroupingExpression(): string
    {
        return match (DB::getDriverName()) {
            'mysql' => "DATE_FORMAT(created_at, '%Y-%m')",
            'pgsql' => "TO_CHAR(created_at, 'YYYY-MM')",
            'sqlsrv' => "FORMAT(created_at, 'yyyy-MM')",
            default => "strftime('%Y-%m', created_at)",
        };
    }

    /**
     * Obtener registros por mes (últimos 6 meses) - versión original.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRegistrationsByMonth(): array
    {
        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = User::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $months[] = [
                'month' => $date->format('Y-m'),
                'month_name' => $date->translatedFormat('F Y'),
                'count' => $count,
            ];
        }

        return $months;
    }

    /**
     * Obtener estadísticas históricas de los últimos N días.
     *
     * @param int $days
     * @return JsonResponse
     */
    public function historicalStatistics(int $days = 30): JsonResponse
    {
        $this->authorize('viewAny', User::class); // Solo admins

        // Obtener estadísticas pre-calculadas
        $statistics = DailyStatistic::lastDays($days)->get();

        if ($statistics->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay estadísticas históricas disponibles. Ejecute el job de cálculo primero.',
                'data' => [],
            ], 404);
        }

        // Preparar datos para gráficos
        $chartData = [
            'dates' => $statistics->pluck('date')->map->format('Y-m-d')->toArray(),
            'total_users' => $statistics->pluck('total_users')->toArray(),
            'active_users' => $statistics->pluck('active_users')->toArray(),
            'new_registrations' => $statistics->pluck('new_registrations')->toArray(),
            'verification_rates' => $statistics->pluck('verification_rate')->toArray(),
        ];

        // Calcular tendencias
        $latest = $statistics->first();
        $previous = $statistics->skip(1)->first();

        $trends = $latest && $previous ? [
            'user_growth' => $latest->total_users - $previous->total_users,
            'activity_change' => $latest->active_users - $previous->active_users,
            'growth_rate' => $latest->trend['growth_rate'] ?? 0,
        ] : null;

        return response()->json([
            'success' => true,
            'message' => 'Estadísticas históricas obtenidas correctamente',
            'data' => [
                'period' => "{$days} días",
                'statistics' => $statistics,
                'chart_data' => $chartData,
                'trends' => $trends,
                'summary' => [
                    'total_records' => $statistics->count(),
                    'date_range' => [
                        'from' => $statistics->last()?->date?->format('Y-m-d'),
                        'to' => $statistics->first()?->date?->format('Y-m-d'),
                    ],
                ],
            ],
            'generated_at' => now()->toISOString(),
        ]);
    }
}
