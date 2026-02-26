<?php

namespace App\Services\Cyaal;

use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * Construye y ejecuta consultas Cyaal reutilizables.
 * Soporta conteo general y por unidad de negocio (misma lógica, condición opcional).
 */
final class CyaalQueryBuilder
{
    private const TABLE_USUARIOS = 'db_production.tbl_cyaal_usuarios';
    private const TABLE_LOGS = 'db_production.mat_cyaal_logs_usuarios';
    private const CONNECTION = 'cloudera';

    /** Columna de unidad de negocio en tbl_cyaal_usuarios (ajustar si el esquema usa otro nombre) */
    private const COL_UNIDAD = 'unidad_negocio';

    /** Tipos de estatus soportados para detalles */
    private const STATUS_FILTERS = [
        'activos'    => "estatus_cyaal_usr = 'ACTIVE'",
        'locked'     => "estatus_cyaal_usr = 'LOCKED_OUT'",
        'password'   => "estatus_cyaal_usr = 'PASSWORD_EXPIRED'",
        'provisioned'=> "estatus_cyaal_usr = 'PROVISIONED'",
        'restaurado' => "estatus_cyaal_usr = 'RECOVERY'",
        'suspendidos'=> "estatus_cyaal_usr = 'SUSPENDED'",
        'inactivos'  => "estatus_cyaal_usr IN ('DEACTIVATED', 'SUSPENDED', 'LOCKED_OUT')",
        'total'     => '1=1',
    ];

    /**
     * CTE base: usuarios únicos por id_cyaal_usr y fecha de carga.
     * Incluye condición opcional por unidad de negocio.
     */
    public function buildUsuariosUnicosCte(string $endDate, ?string $unit = null): array
    {
        $unitCondition = $unit !== null && $unit !== ''
            ? ' AND ' . self::COL_UNIDAD . ' = ?'
            : '';

        $sql = "WITH usuarios_unicos AS (
            SELECT
                *,
                ROW_NUMBER() OVER(
                    PARTITION BY id_cyaal_usr, CAST(audit_fch_carga AS DATE)
                    ORDER BY audit_fch_carga DESC
                ) AS orden
            FROM " . self::TABLE_USUARIOS . "
            WHERE CAST(audit_fch_carga AS DATE) <= ?" . $unitCondition . "
        )";

        $bindings = array_filter([$endDate, $unit], fn ($v) => $v !== null && $v !== '');

        return ['sql' => $sql, 'bindings' => $bindings];
    }

    /**
     * Conteo por todos los tipos de estatus (una sola consulta, reutilizable para cards/charts).
     */
    public function getStatusCounts(string $endDate, ?string $unit = null): stdClass
    {
        $cte = $this->buildUsuariosUnicosCte($endDate, $unit);
        $bindings = $cte['bindings'];

        $sql = $cte['sql'] . "
            SELECT
                COUNT(*) AS total_general,
                SUM(CASE WHEN estatus_cyaal_usr = 'ACTIVE' THEN 1 ELSE 0 END) AS status_active,
                SUM(CASE WHEN estatus_cyaal_usr = 'LOCKED_OUT' THEN 1 ELSE 0 END) AS status_locked,
                SUM(CASE WHEN estatus_cyaal_usr = 'PASSWORD_EXPIRED' THEN 1 ELSE 0 END) AS password_expired,
                SUM(CASE WHEN estatus_cyaal_usr = 'PROVISIONED' THEN 1 ELSE 0 END) AS provisioned,
                SUM(CASE WHEN estatus_cyaal_usr = 'RECOVERY' THEN 1 ELSE 0 END) AS recovery,
                SUM(CASE WHEN estatus_cyaal_usr = 'SUSPENDED' THEN 1 ELSE 0 END) AS status_suspended,
                SUM(CASE WHEN estatus_cyaal_usr = 'STAGED' THEN 1 ELSE 0 END) AS staged,
                SUM(CASE WHEN estatus_cyaal_usr IN ('DEACTIVATED', 'SUSPENDED', 'LOCKED_OUT') THEN 1 ELSE 0 END) AS inactive
            FROM usuarios_unicos
            WHERE orden = 1
        ";

        $result = DB::connection(self::CONNECTION)->selectOne($sql, $bindings);

        return $result ?? $this->emptyStatusCounts();
    }

    /**
     * Detalle de usuarios: misma CTE + filtro por tipo de estatus (+ unidad si aplica).
     */
    public function getDetails(string $endDate, string $type, ?string $unit = null, int $limit = 100): array
    {
        $filter = self::STATUS_FILTERS[$type] ?? null;
        if ($filter === null) {
            return [];
        }

        $cte = $this->buildUsuariosUnicosCte($endDate, $unit);
        $bindings = $cte['bindings'];

        $sql = $cte['sql'] . "
            SELECT *
            FROM usuarios_unicos
            WHERE orden = 1 AND ({$filter})
            LIMIT {$limit}
        ";

        return DB::connection(self::CONNECTION)->select($sql, $bindings);
    }

    /**
     * Gráfica de días suspendidos: MENOR, MODERADO, ELEVADO (según fch_cambio_estatus_cyaal_usr).
     */
    public function getSuspendedChart(string $endDate, ?string $unit = null): array
    {
        $unitCondition = $unit !== null && $unit !== ''
            ? ' AND ' . self::COL_UNIDAD . ' = ?'
            : '';
        $bindings = array_filter([$endDate, $unit], fn ($v) => $v !== null && $v !== '');

        $sql = "WITH usuarios_unicos AS (
            SELECT
                *,
                ROW_NUMBER() OVER(
                    PARTITION BY id_cyaal_usr, CAST(audit_fch_carga AS DATE)
                    ORDER BY audit_fch_carga DESC
                ) AS orden,
                CASE
                    WHEN DATE_SUB(CURRENT_DATE(), 3) <= CAST(fch_cambio_estatus_cyaal_usr AS DATE) THEN 'MENOR'
                    WHEN DATE_SUB(CURRENT_DATE(), 6) <= CAST(fch_cambio_estatus_cyaal_usr AS DATE)
                         AND DATE_SUB(CURRENT_DATE(), 3) > CAST(fch_cambio_estatus_cyaal_usr AS DATE) THEN 'MODERADO'
                    WHEN DATE_SUB(CURRENT_DATE(), 7) >= CAST(fch_cambio_estatus_cyaal_usr AS DATE) THEN 'ELEVADO'
                END AS dias_suspendido
            FROM " . self::TABLE_USUARIOS . "
            WHERE CAST(audit_fch_carga AS DATE) <= ?" . $unitCondition . "
        )
        SELECT dias_suspendido, COUNT(*) AS conteo
        FROM usuarios_unicos
        WHERE orden = 1 AND estatus_cyaal_usr = 'SUSPENDED'
        GROUP BY dias_suspendido
        ";

        $rows = DB::connection(self::CONNECTION)->select($sql, $bindings);

        $labels = ['MENOR', 'MODERADO', 'ELEVADO'];
        $map = collect($rows)->pluck('conteo', 'dias_suspendido')->all();

        return [
            'labels' => $labels,
            'values' => array_map(fn ($l) => (int) ($map[$l] ?? 0), $labels),
        ];
    }

    /**
     * Detalle de usuarios suspendidos por rango (MENOR, MODERADO o ELEVADO).
     */
    public function getSuspendedDetails(string $endDate, string $diasSuspendido, ?string $unit = null, int $limit = 100): array
    {
        $valid = ['MENOR', 'MODERADO', 'ELEVADO'];
        if (! in_array($diasSuspendido, $valid, true)) {
            return [];
        }

        $unitCondition = $unit !== null && $unit !== ''
            ? ' AND ' . self::COL_UNIDAD . ' = ?'
            : '';
        $bindings = array_filter([$endDate, $unit], fn ($v) => $v !== null && $v !== '');

        $sql = "WITH usuarios_unicos AS (
            SELECT
                *,
                ROW_NUMBER() OVER(
                    PARTITION BY id_cyaal_usr, CAST(audit_fch_carga AS DATE)
                    ORDER BY audit_fch_carga DESC
                ) AS orden,
                CASE
                    WHEN DATE_SUB(CURRENT_DATE(), 3) <= CAST(fch_cambio_estatus_cyaal_usr AS DATE) THEN 'MENOR'
                    WHEN DATE_SUB(CURRENT_DATE(), 6) <= CAST(fch_cambio_estatus_cyaal_usr AS DATE)
                         AND DATE_SUB(CURRENT_DATE(), 3) > CAST(fch_cambio_estatus_cyaal_usr AS DATE) THEN 'MODERADO'
                    WHEN DATE_SUB(CURRENT_DATE(), 7) >= CAST(fch_cambio_estatus_cyaal_usr AS DATE) THEN 'ELEVADO'
                END AS dias_suspendido
            FROM " . self::TABLE_USUARIOS . "
            WHERE CAST(audit_fch_carga AS DATE) <= ?" . $unitCondition . "
        )
        SELECT *
        FROM usuarios_unicos
        WHERE orden = 1 AND estatus_cyaal_usr = 'SUSPENDED' AND dias_suspendido = ?
        LIMIT {$limit}
        ";

        $bindings[] = $diasSuspendido;

        return DB::connection(self::CONNECTION)->select($sql, $bindings);
    }

    /**
     * Conteo de usuarios desactivados (evento user.lifecycle.deactivate) en una fecha.
     */
    public function getDeactivatedCount(string $date, ?string $unit = null): int
    {
        $unitCondition = $unit !== null && $unit !== ''
            ? ' AND unidad_negocio = ?'
            : '';
        $bindings = array_filter([$date, $unit], fn ($v) => $v !== null && $v !== '');

        $sql = "SELECT COUNT(*) AS conteo FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.deactivate'
              AND resultado_salida = 'SUCCESS'
              AND CAST(fch_publicacion_utc AS DATE) = ?" . $unitCondition;

        $row = DB::connection(self::CONNECTION)->selectOne($sql, $bindings);

        return (int) ($row->conteo ?? 0);
    }

    /**
     * Detalle de usuarios desactivados en una fecha.
     */
    public function getDeactivatedDetails(string $date, ?string $unit = null, int $limit = 100): array
    {
        $unitCondition = $unit !== null && $unit !== ''
            ? ' AND unidad_negocio = ?'
            : '';
        $bindings = array_filter([$date, $unit], fn ($v) => $v !== null && $v !== '');

        $sql = "SELECT * FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.deactivate'
              AND resultado_salida = 'SUCCESS'
              AND CAST(fch_publicacion_utc AS DATE) = ?" . $unitCondition . "
            LIMIT {$limit}";

        return DB::connection(self::CONNECTION)->select($sql, $bindings);
    }

    /**
     * Conteo de altas de usuarios (user.lifecycle.create) total, del mes y del día.
     */
    public function getUsersAddCounts(string $endDate, ?string $unit = null): stdClass
    {
        $firstDayOfMonth = date('Y-m-01', strtotime($endDate));
        $unitCondition = $unit !== null && $unit !== ''
            ? ' AND unidad_negocio = ?'
            : '';
        $bindings = [$endDate, $firstDayOfMonth, $endDate];
        if ($unit !== null && $unit !== '') {
            $bindings[] = $unit;
        }

        $sql = "SELECT
            COUNT(*) AS total_historico,
            COUNT(CASE WHEN CAST(fch_publicacion_utc AS DATE) = ? THEN 1 END) AS total_dia,
            COUNT(CASE WHEN CAST(fch_publicacion_utc AS DATE) BETWEEN ? AND ? THEN 1 END) AS total_mes
            FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.create'
              AND resultado_salida = 'SUCCESS'" . $unitCondition;

        $result = DB::connection(self::CONNECTION)->selectOne($sql, $bindings);

        $obj = new stdClass;
        $obj->total_historico = (int) ($result->total_historico ?? 0);
        $obj->total_dia = (int) ($result->total_dia ?? 0);
        $obj->total_mes = (int) ($result->total_mes ?? 0);

        return $obj;
    }

    /**
     * Detalle de altas de usuarios (por tipo: dia_alta, mes_alta, total_alta).
     */
    public function getUsersAddDetails(string $endDate, string $type, ?string $unit = null, int $limit = 100): array
    {
        $firstDayOfMonth = date('Y-m-01', strtotime($endDate));
        $unitCondition = $unit !== null && $unit !== ''
            ? ' AND unidad_negocio = ?'
            : '';

        $dateCondition = match ($type) {
            'dia_alta'   => "AND CAST(fch_publicacion_utc AS DATE) = ?",
            'mes_alta'   => "AND CAST(fch_publicacion_utc AS DATE) BETWEEN ? AND ?",
            'total_alta' => '',
            default      => '',
        };

        $bindings = match ($type) {
            'dia_alta'   => array_values(array_filter([$endDate, $unit], fn ($v) => $v !== null && $v !== '')),
            'mes_alta'   => array_values(array_filter([$firstDayOfMonth, $endDate, $unit], fn ($v) => $v !== null && $v !== '')),
            'total_alta' => $unit !== null && $unit !== '' ? [$unit] : [],
            default      => [],
        };

        $sql = "SELECT * FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.create'
              AND resultado_salida = 'SUCCESS'
              {$dateCondition}" . $unitCondition . "
            LIMIT {$limit}";

        return DB::connection(self::CONNECTION)->select($sql, $bindings);
    }

    /**
     * Tendencia semanal (altas por día, últimos 7 días).
     */
    public function getWeeklyTrend(string $endDate, ?string $unit = null): array
    {
        $unitCondition = $unit !== null && $unit !== ''
            ? ' AND unidad_negocio = ?'
            : '';
        $bindings = array_filter([$endDate, $unit], fn ($v) => $v !== null && $v !== '');

        $sql = "SELECT
            CAST(fch_publicacion_utc AS DATE) AS fecha,
            COUNT(*) AS total
            FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.create'
              AND resultado_salida = 'SUCCESS'
              AND CAST(fch_publicacion_utc AS DATE) BETWEEN DATE_SUB(?, 6) AND ?" . $unitCondition . "
            GROUP BY 1
            ORDER BY 1 ASC";

        return DB::connection(self::CONNECTION)->select($sql, $bindings);
    }

    /**
     * Tendencia anual (altas por mes, últimos 12 meses).
     */
    public function getYearlyTrend(string $endDate, ?string $unit = null): array
    {
        $unitCondition = $unit !== null && $unit !== ''
            ? ' AND unidad_negocio = ?'
            : '';
        $bindings = array_filter([$endDate, $unit], fn ($v) => $v !== null && $v !== '');

        $sql = "SELECT
            TRUNC(CAST(fch_publicacion_utc AS TIMESTAMP), 'MM') AS mes_referencia,
            COUNT(*) AS total
            FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.create'
              AND resultado_salida = 'SUCCESS'
              AND CAST(fch_publicacion_utc AS DATE) >= ADD_MONTHS(TRUNC(?, 'MM'), -12)" . $unitCondition . "
            GROUP BY 1
            ORDER BY 1 ASC";

        return DB::connection(self::CONNECTION)->select($sql, $bindings);
    }

    public static function statusFilters(): array
    {
        return self::STATUS_FILTERS;
    }

    private function emptyStatusCounts(): stdClass
    {
        $o = new stdClass;
        $o->total_general = 0;
        $o->status_active = 0;
        $o->status_locked = 0;
        $o->password_expired = 0;
        $o->provisioned = 0;
        $o->recovery = 0;
        $o->status_suspended = 0;
        $o->staged = 0;
        $o->inactive = 0;

        return $o;
    }
}
