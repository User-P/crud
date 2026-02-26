<?php

namespace App\Services\Cyaal;

use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * Construye y ejecuta consultas Cyaal reutilizables.
 * Soporta conteo general y por unidad de negocio (misma lógica, condición opcional).
 *
 * Impala/Cloudera ODBC no maneja bien placeholders (?); los valores se inlined como literales
 * validados (fecha Y-m-d, unidad alfanumérica) para evitar errores de sintaxis e inyección.
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
     * Fecha segura para Impala: solo Y-m-d, inlined como literal con comillas.
     */
    private function escapeDate(string $date): string
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new \InvalidArgumentException('Fecha inválida, se espera Y-m-d: ' . $date);
        }
        return "'" . $date . "'";
    }

    /**
     * Unidad segura para Impala: solo letras, números y guión bajo; inlined con comillas.
     */
    private function escapeUnit(?string $unit): string
    {
        if ($unit === null || $unit === '') {
            return '';
        }
        if (! preg_match('/^[A-Za-z0-9_\-]+$/', $unit)) {
            throw new \InvalidArgumentException('Unidad de negocio inválida: ' . $unit);
        }
        return "'" . str_replace("'", "''", $unit) . "'";
    }

    /**
     * CTE base: usuarios únicos por id_cyaal_usr y fecha de carga.
     * Valores inlined (Impala ODBC no soporta bien placeholders).
     */
    public function buildUsuariosUnicosCte(string $endDate, ?string $unit = null): string
    {
        $end = $this->escapeDate($endDate);
        $unitCondition = $this->escapeUnit($unit) !== ''
            ? ' AND ' . self::COL_UNIDAD . ' = ' . $this->escapeUnit($unit)
            : '';

        return "WITH usuarios_unicos AS (
            SELECT
                *,
                ROW_NUMBER() OVER(
                    PARTITION BY id_cyaal_usr, CAST(audit_fch_carga AS DATE)
                    ORDER BY audit_fch_carga DESC
                ) AS orden
            FROM " . self::TABLE_USUARIOS . "
            WHERE CAST(audit_fch_carga AS DATE) <= " . $end . $unitCondition . "
        )";
    }

    /**
     * Conteo por todos los tipos de estatus (una sola consulta, reutilizable para cards/charts).
     */
    public function getStatusCounts(string $endDate, ?string $unit = null): stdClass
    {
        $cte = $this->buildUsuariosUnicosCte($endDate, $unit);

        $sql = $cte . "
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

        $result = DB::connection(self::CONNECTION)->selectOne($sql);

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

        $sql = $cte . "
            SELECT *
            FROM usuarios_unicos
            WHERE orden = 1 AND ({$filter})
            LIMIT {$limit}
        ";

        return DB::connection(self::CONNECTION)->select($sql);
    }

    /**
     * Gráfica de días suspendidos: MENOR, MODERADO, ELEVADO (según fch_cambio_estatus_cyaal_usr).
     */
    public function getSuspendedChart(string $endDate, ?string $unit = null): array
    {
        $end = $this->escapeDate($endDate);
        $unitCondition = $this->escapeUnit($unit) !== ''
            ? ' AND ' . self::COL_UNIDAD . ' = ' . $this->escapeUnit($unit)
            : '';

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
            WHERE CAST(audit_fch_carga AS DATE) <= " . $end . $unitCondition . "
        )
        SELECT dias_suspendido, COUNT(*) AS conteo
        FROM usuarios_unicos
        WHERE orden = 1 AND estatus_cyaal_usr = 'SUSPENDED'
        GROUP BY dias_suspendido
        ";

        $rows = DB::connection(self::CONNECTION)->select($sql);

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

        $end = $this->escapeDate($endDate);
        $unitCondition = $this->escapeUnit($unit) !== ''
            ? ' AND ' . self::COL_UNIDAD . ' = ' . $this->escapeUnit($unit)
            : '';
        $diasLiteral = "'" . str_replace("'", "''", $diasSuspendido) . "'";

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
            WHERE CAST(audit_fch_carga AS DATE) <= " . $end . $unitCondition . "
        )
        SELECT *
        FROM usuarios_unicos
        WHERE orden = 1 AND estatus_cyaal_usr = 'SUSPENDED' AND dias_suspendido = " . $diasLiteral . "
        LIMIT {$limit}
        ";

        return DB::connection(self::CONNECTION)->select($sql);
    }

    /**
     * Conteo de usuarios desactivados (evento user.lifecycle.deactivate) en una fecha.
     */
    public function getDeactivatedCount(string $date, ?string $unit = null): int
    {
        $dateLiteral = $this->escapeDate($date);
        $unitCondition = $this->escapeUnit($unit) !== ''
            ? ' AND unidad_negocio = ' . $this->escapeUnit($unit)
            : '';

        $sql = "SELECT COUNT(*) AS conteo FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.deactivate'
              AND resultado_salida = 'SUCCESS'
              AND CAST(fch_publicacion_utc AS DATE) = " . $dateLiteral . $unitCondition;

        $row = DB::connection(self::CONNECTION)->selectOne($sql);

        return (int) ($row->conteo ?? 0);
    }

    /**
     * Detalle de usuarios desactivados en una fecha.
     */
    public function getDeactivatedDetails(string $date, ?string $unit = null, int $limit = 100): array
    {
        $dateLiteral = $this->escapeDate($date);
        $unitCondition = $this->escapeUnit($unit) !== ''
            ? ' AND unidad_negocio = ' . $this->escapeUnit($unit)
            : '';

        $sql = "SELECT * FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.deactivate'
              AND resultado_salida = 'SUCCESS'
              AND CAST(fch_publicacion_utc AS DATE) = " . $dateLiteral . $unitCondition . "
            LIMIT {$limit}";

        return DB::connection(self::CONNECTION)->select($sql);
    }

    /**
     * Conteo de altas de usuarios (user.lifecycle.create) total, del mes y del día.
     */
    public function getUsersAddCounts(string $endDate, ?string $unit = null): stdClass
    {
        $firstDayOfMonth = date('Y-m-01', strtotime($endDate));
        $end = $this->escapeDate($endDate);
        $first = $this->escapeDate($firstDayOfMonth);
        $unitCondition = $this->escapeUnit($unit) !== ''
            ? ' AND unidad_negocio = ' . $this->escapeUnit($unit)
            : '';

        $sql = "SELECT
            COUNT(*) AS total_historico,
            COUNT(CASE WHEN CAST(fch_publicacion_utc AS DATE) = " . $end . " THEN 1 END) AS total_dia,
            COUNT(CASE WHEN CAST(fch_publicacion_utc AS DATE) BETWEEN " . $first . " AND " . $end . " THEN 1 END) AS total_mes
            FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.create'
              AND resultado_salida = 'SUCCESS'" . $unitCondition;

        $result = DB::connection(self::CONNECTION)->selectOne($sql);

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
        $end = $this->escapeDate($endDate);
        $first = $this->escapeDate($firstDayOfMonth);
        $unitCondition = $this->escapeUnit($unit) !== ''
            ? ' AND unidad_negocio = ' . $this->escapeUnit($unit)
            : '';

        $dateCondition = match ($type) {
            'dia_alta'   => 'AND CAST(fch_publicacion_utc AS DATE) = ' . $end,
            'mes_alta'   => 'AND CAST(fch_publicacion_utc AS DATE) BETWEEN ' . $first . ' AND ' . $end,
            'total_alta' => '',
            default      => '',
        };

        $sql = "SELECT * FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.create'
              AND resultado_salida = 'SUCCESS'
              {$dateCondition}" . $unitCondition . "
            LIMIT {$limit}";

        return DB::connection(self::CONNECTION)->select($sql);
    }

    /**
     * Tendencia semanal (altas por día, últimos 7 días).
     */
    public function getWeeklyTrend(string $endDate, ?string $unit = null): array
    {
        $end = $this->escapeDate($endDate);
        $unitCondition = $this->escapeUnit($unit) !== ''
            ? ' AND unidad_negocio = ' . $this->escapeUnit($unit)
            : '';

        $sql = "SELECT
            CAST(fch_publicacion_utc AS DATE) AS fecha,
            COUNT(*) AS total
            FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.create'
              AND resultado_salida = 'SUCCESS'
              AND CAST(fch_publicacion_utc AS DATE) BETWEEN DATE_SUB(" . $end . ", 6) AND " . $end . $unitCondition . "
            GROUP BY 1
            ORDER BY 1 ASC";

        return DB::connection(self::CONNECTION)->select($sql);
    }

    /**
     * Tendencia anual (altas por mes, últimos 12 meses).
     */
    public function getYearlyTrend(string $endDate, ?string $unit = null): array
    {
        $end = $this->escapeDate($endDate);
        $unitCondition = $this->escapeUnit($unit) !== ''
            ? ' AND unidad_negocio = ' . $this->escapeUnit($unit)
            : '';

        $sql = "SELECT
            TRUNC(CAST(fch_publicacion_utc AS TIMESTAMP), 'MM') AS mes_referencia,
            COUNT(*) AS total
            FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.create'
              AND resultado_salida = 'SUCCESS'
              AND CAST(fch_publicacion_utc AS DATE) >= ADD_MONTHS(TRUNC(" . $end . ", 'MM'), -12)" . $unitCondition . "
            GROUP BY 1
            ORDER BY 1 ASC";

        return DB::connection(self::CONNECTION)->select($sql);
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
