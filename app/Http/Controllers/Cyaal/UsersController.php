<?php

namespace App\Http\Controllers\Cyaal;

use App\Http\Controllers\Controller;
use App\Services\Cyaal\CyaalQueryBuilder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function __construct(
        private CyaalQueryBuilder $queries
    ) {}

    public function index()
    {
        return Inertia::render('Cyaal/Users/General');
    }

    /**
     * Cards para vista general o por unidad de negocio.
     * Query: ?unit=EKT (opcional)
     */
    public function cards(Request $request, string $end)
    {
        $unit = $request->query('unit');
        $data = $this->queries->getStatusCounts($end, $unit);

        return response()->json($this->buildCardsResponse($data));
    }

    /**
     * Charts (misma estructura que cards) + datos para gráficas.
     * Query: ?unit=EKT (opcional)
     */
    public function charts(Request $request, string $end)
    {
        $unit = $request->query('unit');
        $data = $this->queries->getStatusCounts($end, $unit);

        return response()->json([
            'cards' => $this->buildChartsCards($data),
            'charts' => [
                'pie' => [
                    ['name' => 'Activos', 'value' => (int) $data->status_active],
                    ['name' => 'Inactivos', 'value' => (int) $data->inactive],
                ],
                'semaphore' => [
                    'labels' => [
                        'Bloqueados',
                        'Contraseña expirado',
                        'Provisionado',
                        'Suspendidos',
                        'Desactivados',
                    ],
                    'values' => [
                        (int) $data->status_locked,
                        (int) $data->password_expired,
                        (int) $data->provisioned,
                        (int) $data->status_suspended,
                        (int) $data->staged,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Detalle por tipo de estatus (activos, locked, password, etc.).
     * Body: type, date, unit (opcional)
     */
    public function details(Request $request)
    {
        $type = $request->input('type');
        $date = $request->input('date');
        $unit = $request->input('unit');

        if (! $type || ! $date) {
            return response()->json(['error' => 'Se requieren type y date'], 400);
        }

        if (! array_key_exists($type, CyaalQueryBuilder::statusFilters())) {
            return response()->json(['error' => 'Tipo de consulta no soportado: ' . $type], 400);
        }

        $limit = $request->input('limit');
        $limit = is_numeric($limit) && (int) $limit > 0 ? (int) $limit : null;

        $rows = $this->queries->getDetails($date, $type, $unit, $limit);

        return response()->json($rows);
    }

    /**
     * Gráfica de días suspendidos (MENOR, MODERADO, ELEVADO).
     * Query: ?unit=EKT (opcional)
     */
    public function suspended(Request $request, string $end)
    {
        $unit = $request->query('unit');
        $chart = $this->queries->getSuspendedChart($end, $unit);

        return response()->json([
            'status' => 'success',
            'chart' => $chart,
            'groups' => [
                'MENOR' => ['label' => 'MENOR', 'count' => $chart['values'][0], 'details' => []],
                'MODERADO' => ['label' => 'MODERADO', 'count' => $chart['values'][1], 'details' => []],
                'ELEVADO' => ['label' => 'ELEVADO', 'count' => $chart['values'][2], 'details' => []],
            ],
        ], 200);
    }

    /**
     * Detalle de suspendidos por rango (MENOR, MODERADO, ELEVADO).
     * Body: date, dias_suspendido, unit (opcional)
     */
    public function suspendedDetails(Request $request)
    {
        $date = $request->input('date');
        $diasSuspendido = $request->input('dias_suspendido');
        $unit = $request->input('unit');

        if (! $date || ! $diasSuspendido) {
            return response()->json(['error' => 'Se requieren date y dias_suspendido'], 400);
        }

        $limit = $request->input('limit');
        $limit = is_numeric($limit) && (int) $limit > 0 ? (int) $limit : null;

        $rows = $this->queries->getSuspendedDetails($date, $diasSuspendido, $unit, $limit);

        return response()->json($rows);
    }

    /**
     * Altas de usuarios: cards + gráficas lineal y barras.
     * Query: ?unit=EKT (opcional)
     */
    public function usersAdd(Request $request, string $date)
    {
        $unit = $request->query('unit');
        $lineChart = $this->queries->getYearlyTrend($date, $unit);
        $barChart = $this->queries->getWeeklyTrend($date, $unit);
        $cards = $this->buildUsersAddCards($date, $unit);

        return response()->json([
            'cards' => $cards,
            'line' => [
                'labels' => collect($lineChart)->pluck('mes_referencia')->map(fn ($m) => date('M Y', strtotime($m)))->values(),
                'series' => [
                    [
                        'name' => 'Usuarios Creados',
                        'data' => collect($lineChart)->pluck('total')->map(fn ($v) => (int) $v)->values()->all(),
                    ],
                ],
            ],
            'bar' => [
                'labels' => collect($barChart)->pluck('fecha')->values(),
                'series' => [
                    [
                        'name' => 'Altas Diarias',
                        'data' => collect($barChart)->pluck('total')->map(fn ($v) => (int) $v)->values()->all(),
                    ],
                ],
            ],
        ]);
    }

    /**
     * Detalle de altas (dia_alta, mes_alta, total_alta).
     * Body: type, date, unit (opcional)
     */
    public function usersAddDetails(Request $request)
    {
        $type = $request->input('type');
        $date = $request->input('date');
        $unit = $request->input('unit');

        if (! $type || ! $date) {
            return response()->json(['error' => 'Se requieren type y date'], 400);
        }

        $limit = $request->input('limit');
        $limit = is_numeric($limit) && (int) $limit > 0 ? (int) $limit : null;

        $rows = $this->queries->getUsersAddDetails($date, $type, $unit, $limit);

        return response()->json($rows);
    }

    /**
     * Respuesta estándar de cards (hero + primary + secondary) a partir del objeto de conteos.
     */
    private function buildCardsResponse(object $data): array
    {
        return [
            'hero' => [
                'id' => 'total',
                'label' => 'Usuarios totales',
                'value' => (int) $data->total_general,
                'variant' => 'blue',
                'iconKey' => 'heroicons:globe-alt',
            ],
            'primary' => [
                [
                    'id' => 'activos',
                    'label' => 'Usuarios activos',
                    'value' => (int) $data->status_active,
                    'variant' => 'green',
                    'iconKey' => 'heroicons:check-circle',
                ],
                [
                    'id' => 'inactivos',
                    'label' => 'Usuarios inactivos',
                    'value' => (int) $data->inactive,
                    'variant' => 'red',
                    'iconKey' => 'heroicons:x-circle',
                ],
            ],
            'secondary' => [
                ['id' => 'locked', 'label' => 'Bloqueados', 'value' => (int) $data->status_locked, 'variant' => 'red', 'iconKey' => 'heroicons:no-symbol'],
                ['id' => 'password', 'label' => 'Password expirado', 'value' => (int) $data->password_expired, 'variant' => 'red', 'iconKey' => 'heroicons:lock-open'],
                ['id' => 'provisioned', 'label' => 'Provisionados', 'value' => (int) $data->provisioned, 'variant' => 'red', 'iconKey' => 'heroicons:pause-circle'],
                ['id' => 'suspendidos', 'label' => 'Suspendidos', 'value' => (int) $data->status_suspended, 'variant' => 'red', 'iconKey' => 'heroicons:user-minus'],
                ['id' => 'desactivados', 'label' => 'Desactivados', 'value' => (int) $data->staged, 'variant' => 'red', 'iconKey' => 'heroicons:minus-circle'],
            ],
        ];
    }

    private function buildChartsCards(object $data): array
    {
        return [
            ['id' => 'total', 'label' => 'Usuarios totales', 'value' => (int) $data->total_general, 'variant' => 'blue', 'iconKey' => 'heroicons:globe-alt'],
            ['id' => 'activos', 'label' => 'Usuarios activos', 'value' => (int) $data->status_active, 'variant' => 'green', 'iconKey' => 'heroicons:check-circle'],
            ['id' => 'inactivos', 'label' => 'Usuarios inactivos', 'value' => (int) $data->inactive, 'variant' => 'red', 'iconKey' => 'heroicons:x-circle'],
        ];
    }

    private function buildUsersAddCards(string $endDate, ?string $unit): array
    {
        $data = $this->queries->getUsersAddCounts($endDate, $unit);
        $firstDayOfMonth = date('Y-m-01', strtotime($endDate));

        return [
            [
                'id' => 'total_alta',
                'label' => 'Altas totales de usuarios.',
                'value' => $data->total_historico,
                'variant' => 'blue',
                'iconKey' => 'heroicons:users',
            ],
            [
                'id' => 'mes_alta',
                'label' => 'Altas de usuarios del mes de: ' . $firstDayOfMonth,
                'value' => $data->total_mes,
                'variant' => 'yellow',
                'iconKey' => 'heroicons:calendar-days',
            ],
            [
                'id' => 'dia_alta',
                'label' => 'Altas de usuarios del dia: ' . $endDate,
                'value' => $data->total_dia,
                'variant' => 'green',
                'iconKey' => 'heroicons:calendar-date-range',
            ],
        ];
    }
}
