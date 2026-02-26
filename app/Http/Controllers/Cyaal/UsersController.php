<?php

namespace App\Http\Controllers\Cyaal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function index()
    {
        return Inertia::render('Cyaal/Users/General');
    }

    public function cards(string $end)
    {
        $data = $this->getData($end);
        return response()->json([
            'hero' => [
                'id'       => 'total',
                'label'    => 'Usuarios totales',
                'value'    => (int)$data->total_general,
                'variant'  => 'blue',
                'iconKey'  => 'heroicons:globe-alt',
            ],
            'primary' => [
                [
                    'id'      => 'activos',
                    'label'   => 'Usuarios activos',
                    'value'   => (int)$data->status_active,
                    'variant' => 'green',
                    'iconKey' => 'heroicons:check-circle',
                ],
                [
                    'id'      => 'inactivos',
                    'label'   => 'Usuarios inactivos',
                    'value'   => (int)$data->inactive,
                    'variant' => 'red',
                    'iconKey' => 'heroicons:x-circle',
                ],
            ],
            'secondary' => [
                [
                    'id'      => 'locked',
                    'label'   => 'Bloqueados',
                    'value'   => (int)$data->status_locked,
                    'variant' => 'red',
                    'iconKey' => 'heroicons:no-symbol',
                ],
                [
                    'id'      => 'password',
                    'label'   => 'Password expirado',
                    'value'   => (int)$data->password_expired,
                    'variant' => 'red',
                    'iconKey' => 'heroicons:lock-open',
                ],
                [
                    'id'      => 'provisioned',
                    'label'   => 'Provisionados',
                    'value'   => (int)$data->provisioned,
                    'variant' => 'red',
                    'iconKey' => 'heroicons:pause-circle',
                ],
                [
                    'id'      => 'suspendidos',
                    'label'   => 'Suspendidos',
                    'value'   => (int)$data->status_suspended,
                    'variant' => 'red',
                    'iconKey' => 'heroicons:user-minus',
                ],
                [
                    'id'      => 'desactivados',
                    'label'   => 'Desactivados',
                    'value'   => (int)$data->staged,
                    'variant' => 'red',
                    'iconKey' => 'heroicons:minus-circle',
                ],
            ],
        ]);
    }

    public function charts(string $end)
    {
        $data = $this->getData($end);
        return response()->json([
            'cards' => [
                [
                    'id'       => 'total',
                    'label'    => 'Usuarios totales',
                    'value'    => (int)$data->total_general,
                    'variant'  => 'blue',
                    'iconKey'  => 'heroicons:globe-alt'
                ],
                [
                    'id'      => 'activos',
                    'label'   => 'Usuarios activos',
                    'value'   => (int)$data->status_active,
                    'variant' => 'green',
                    'iconKey' => 'heroicons:check-circle',
                ],
                [
                    'id'      => 'inactivos',
                    'label'   => 'Usuarios inactivos',
                    'value'   => (int)$data->inactive,
                    'variant' => 'red',
                    'iconKey' => 'heroicons:x-circle',
                ],
            ],
            'charts' => [
                'pie' => [
                    [
                        'name' => 'Activos',
                        'value' => (int) $data->status_active
                    ],
                    [
                        'name' => 'Inactivos',
                        'value' => (int) $data->inactive,
                    ]
                ],
                'semaphore'  => [
                    'labels' => [
                        'Bloqueados',
                        'Contraseña expirado',
                        'Provisionado',
                        'Suspendidos',
                        'Desactivados'
                    ],
                    'values' => [
                        (int)$data->status_locked,
                        (int)$data->password_expired,
                        (int)$data->provisioned,
                        (int)$data->status_suspended,
                        (int)$data->staged,
                    ],
                ],
            ]
        ]);
    }

    private function getData(string $end)
    {
        return DB::connection('cloudera')->selectOne("WITH usuarios_unicos AS (
            SELECT
                estatus_cyaal_usr,
                fch_cambio_estatus_cyaal_usr,
                ROW_NUMBER() OVER(
                    PARTITION BY id_cyaal_usr
                    ORDER BY audit_fch_carga DESC
                ) as orden
            FROM db_production.tbl_cyaal_usuarios
            WHERE CAST(audit_fch_carga AS DATE) <= '{$end}'
        ),
        calculo_categorias AS ( SELECT estatus_cyaal_usr
            FROM usuarios_unicos
            WHERE orden = 1
        )
        SELECT
            COUNT(*) as total_general,
            SUM(CASE WHEN estatus_cyaal_usr = 'ACTIVE' THEN 1 ELSE 0 END) as status_active,
            SUM(CASE WHEN estatus_cyaal_usr = 'LOCKED_OUT' THEN 1 ELSE 0 END) as status_locked,
            SUM(CASE WHEN estatus_cyaal_usr = 'PASSWORD_EXPIRED' THEN 1 ELSE 0 END) as password_expired,
            SUM(CASE WHEN estatus_cyaal_usr = 'PROVISIONED' THEN 1 ELSE 0 END) as provisioned,
            SUM(CASE WHEN estatus_cyaal_usr = 'RECOVERY' THEN 1 ELSE 0 END) as recovery,
            SUM(CASE WHEN estatus_cyaal_usr = 'SUSPENDED' THEN 1 ELSE 0 END) as status_suspended,
            SUM(CASE WHEN estatus_cyaal_usr = 'STAGED' THEN 1 ELSE 0 END) as staged,
            SUM(CASE WHEN estatus_cyaal_usr IN ('DEACTIVATED', 'SUSPENDED', 'LOCKED_OUT') THEN 1 ELSE 0 END) as inactive
        FROM calculo_categorias");
    }

    public function suspended(string $end)
    {
        $results = collect(DB::connection('cloudera')->select(
            "WITH usuarios_procesados AS (
                SELECT *,ROW_NUMBER() OVER(
            PARTITION BY id_cyaal_usr, CAST(audit_fch_carga AS DATE)
            ORDER BY audit_fch_carga DESC) as fila_reciente,
            DATEDIFF(current_date(), CAST(fch_cambio_estatus_cyaal_usr AS DATE)) as dias_transcurridos
        FROM db_production.tbl_cyaal_usuarios
        WHERE CAST(audit_fch_carga AS DATE) <= '{$end}'
            AND estatus_cyaal_usr = 'SUSPENDED'),usuarios_categorizados AS (
        SELECT *,
            CASE
                WHEN dias_transcurridos BETWEEN 1 AND 3 THEN '1-3 Dias'
                WHEN dias_transcurridos BETWEEN 4 AND 6 THEN '4-6 Dias'
            ELSE '7+ Dias'
            END AS categoria
        FROM usuarios_procesados
        WHERE fila_reciente = 1)
        SELECT * FROM usuarios_categorizados"
        ));

        $categories = collect(['1-3 Dias', '4-6 Dias', '7+ Dias']);

        $data = $categories->mapWithKeys(function ($label) use ($results) {
            $items = $results->where('categoria', $label);

            return [
                $label => [
                    'label'   => $label,
                    'count'   => $items->count(),
                    'details' => $items->values()->all()
                ]
            ];
        });

        return response()->json([
            'status' => 'success',
            'chart'  => [
                'labels' => $data->pluck('label')->values(),
                'values' => $data->pluck('count')->values(),
            ],
            'groups' => $data
        ], 200);
    }

    public function details(Request $request)
    {
        $type = $request->type;
        $date = $request->date;
        $filters = [
            'activos'   => "WHERE estatus_cyaal_usr = 'ACTIVE'",
            'locked' => "WHERE estatus_cyaal_usr = 'LOCKED_OUT'",
            'password' => "WHERE estatus_cyaal_usr = 'PASSWORD_EXPIRED'",
            'provisioned' => "WHERE estatus_cyaal_usr = 'PROVISIONED'",
            'restaurado' => "WHERE estatus_cyaal_usr = 'RECOVERY'",
            'suspendidos' => "WHERE estatus_cyaal_usr = 'SUSPENDED'",
            'inactivos' => "WHERE estatus_cyaal_usr IN ('DEACTIVATED', 'SUSPENDED', 'LOCKED_OUT')",
            'total'     => "",
        ];

        $statusFilter = $filters[$type] ?? null;

        if (is_null($statusFilter)) {
            return response()->json(['error' => 'Tipo de consulta no soportado: ' . $type], 400);
        }
        $query = <<<SQL
        WITH usuarios_unicos AS (
            SELECT *,
                ROW_NUMBER() OVER(
                    PARTITION BY id_cyaal_usr, CAST(audit_fch_carga AS DATE)
                    ORDER BY audit_fch_carga DESC
                ) as orden
            FROM db_production.tbl_cyaal_usuarios
            WHERE CAST(audit_fch_carga AS DATE) <= '{$date}'
        )
        SELECT * FROM usuarios_unicos
        {$statusFilter}
        LIMIT 100
    SQL;

        return DB::connection('cloudera')->select($query);
    }

    private function getWeeklyTrend($date)
    {
        $query = "SELECT
            CAST(fch_publicacion_utc AS DATE) as fecha,
            COUNT(*) as total
        FROM db_production.mat_cyaal_logs_usuarios
        WHERE tipo_evento = 'user.lifecycle.create'
          AND resultado_salida = 'SUCCESS'
          AND CAST(fch_publicacion_utc AS DATE) BETWEEN DATE_SUB('{$date}', 6) AND '{$date}'
        GROUP BY 1
        ORDER BY 1 ASC
    ";
        return DB::connection('cloudera')->select($query);
    }

    private function getYearlyTrend($date)
    {
        $query = "SELECT
            TRUNC(CAST(fch_publicacion_utc AS TIMESTAMP), 'MM') as mes_referencia,
            COUNT(*) as total
        FROM db_production.mat_cyaal_logs_usuarios
        WHERE tipo_evento = 'user.lifecycle.create'
          AND resultado_salida = 'SUCCESS'
          AND CAST(fch_publicacion_utc AS DATE) >= ADD_MONTHS(TRUNC('{$date}', 'MM'), -12)
        GROUP BY 1
        ORDER BY 1 ASC
    ";
        return DB::connection('cloudera')->select($query);
    }

    public function usersAddCards(string $end)
    {
        $date = $end;
        $firstDayOfMonth = date('Y-m-01', strtotime($date));
        $data = DB::connection('cloudera')->selectOne("SELECT
            COUNT(*) as total_historico,
            COUNT(CASE WHEN CAST(fch_publicacion_utc AS DATE) = '{$date}' THEN 1 END) as total_dia,
            COUNT(CASE WHEN CAST(fch_publicacion_utc AS DATE) BETWEEN '{$firstDayOfMonth}' AND '{$date}' THEN 1 END) as total_mes
            FROM db_production.mat_cyaal_logs_usuarios
            WHERE tipo_evento = 'user.lifecycle.create'
            AND resultado_salida = 'SUCCESS'");

        return [
            [
                'id'      => 'total_alta',
                'label'   => 'Altas totales de usuarios.',
                'value'   => (int)($data->total_historico ?? 0),
                'variant' => 'blue',
                'iconKey' => 'heroicons:users',
            ],
            [
                'id'      => 'mes_alta',
                'label'   => 'Altas de usuarios del mes de: ' . $firstDayOfMonth,
                'value'   => (int)($data->total_mes ?? 0),
                'variant' => 'yellow',
                'iconKey' => 'heroicons:calendar-days',
            ],
            [
                'id'      => 'dia_alta',
                'label'   => 'Altas de usuarios del dia: ' . $end,
                'value'   => (int)($data->total_dia ?? 0),
                'variant' => 'green',
                'iconKey' => 'heroicons:calendar-date-range',
            ],
        ];
    }

    public function usersAdd(string $date)
    {

        $lineChart = $this->getYearlyTrend($date);
        $barChart = $this->getWeeklyTrend($date);

        return response()->json([
            'cards' => $this->usersAddCards($date),
            'line' => [
                'labels' => collect($lineChart)->pluck('mes_referencia')->map(fn($m) => date('M Y', strtotime($m))),
                'series' => [
                    [
                        'name' => 'Usuarios Creados',
                        'data' => collect($lineChart)->pluck('total')->map(fn($v) => (int)$v)->toArray()
                    ]
                ]
            ],
            'bar' => [
                'labels' => collect($barChart)->pluck('fecha'),
                'series' => [
                    [
                        'name' => 'Altas Diarias',
                        'data' => collect($barChart)->pluck('total')->map(fn($v) => (int)$v)->toArray()
                    ]
                ]
            ]
        ]);
    }

    public function usersAddDetails(Request $request)
    {
        $type = $request->type;
        $date = $request->date;
        $firstDayOfMonth = date('Y-m-01', strtotime($date));

        $dateCondition = match ($type) {
            'dia_alta'   => "AND CAST(fch_publicacion_utc AS DATE) = '{$date}'",
            'mes_alta'   => "AND CAST(fch_publicacion_utc AS DATE) BETWEEN '{$firstDayOfMonth}' AND '{$date}'",
            'total_alta' => "",
        };

        $query = "SELECT * FROM db_production.mat_cyaal_logs_usuarios
        WHERE tipo_evento = 'user.lifecycle.create'
        AND resultado_salida = 'SUCCESS'
        {$dateCondition}
        LIMIT 100

    ";
        return DB::connection('cloudera')->select($query);
    }
}
