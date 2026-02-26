<?php

namespace App\Services\Cyaal;

use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * Unidades de negocio: clave (request/URL, sin espacio) => valor en BD (puede tener espacio).
 * El frontend envía la clave (ej. BACK_OFFICE) y aquí se traduce al valor real (ej. BACK OFFICE).
 * Añade o edita entradas según tus unidades en base de datos.
 */
final class CyaalQueryBuilder
{
    private const TABLE_USUARIOS = 'db_production.tbl_cyaal_usuarios';
    private const TABLE_LOGS = 'db_production.mat_cyaal_logs_usuarios';
    private const CONNECTION = 'cloudera';

    private const COL_UNIDAD = 'compania_cyaal_usr';

    /** Clave (lo que recibe la API) => Valor en BD (como está en compania_cyaal_usr) */
    private const UNIT_MAP = [
        'EKT'          => 'EKT',
        'TPE'          => 'TPE',
        'TVA'          => 'TVA',
        'BACK_OFFICE'  => 'BACK OFFICE',
        'BACK OFFICE'  => 'BACK OFFICE',
    ];

    private const STATUS_FILTERS = [
        'activos'    => "estatus_cyaal_usr = 'ACTIVE'",
        'locked'     => "estatus_cyaal_usr = 'LOCKED_OUT'",
        'password'   => "estatus_cyaal_usr = 'PASSWORD_EXPIRED'",
        'provisioned' => "estatus_cyaal_usr = 'PROVISIONED'",
        'restaurado' => "estatus_cyaal_usr = 'RECOVERY'",
        'suspendidos' => "estatus_cyaal_usr = 'SUSPENDED'",
        'inactivos'  => "estatus_cyaal_usr IN ('DEACTIVATED', 'SUSPENDED', 'LOCKED_OUT')",
        'total'     => '1=1',
    ];

    private function escapeDate(string $date): string
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new \InvalidArgumentException('Fecha inválida, se espera Y-m-d: ' . $date);
        }
        return "'" . $date . "'";
    }

    /**
     * Normaliza el valor de unidad: si viene la clave (ej. BACK_OFFICE), devuelve el valor en BD (ej. BACK OFFICE).
     * Así las unidades con espacio se consultan correctamente.
     */
    private function normalizeUnit(?string $unit): ?string
    {
        if ($unit === null || $unit === '') {
            return null;
        }
        $trimmed = trim($unit);
        if ($trimmed === '') {
            return null;
        }
        return self::UNIT_MAP[$trimmed] ?? $trimmed;
    }

    /**
     * Escapa el valor de unidad para SQL (permite espacios; solo rechaza caracteres peligrosos).
     */
    private function escapeUnit(?string $unit): string
    {
        if ($unit === null || $unit === '') {
            return '';
        }
        $trimmed = trim($unit);
        if ($trimmed === '') {
            return '';
        }
        if (preg_match('/[\x00\x1a\'\\\\]/', $trimmed)) {
            throw new \InvalidArgumentException('Unidad de negocio contiene caracteres no permitidos: ' . $unit);
        }
        return "'" . str_replace("'", "''", $trimmed) . "'";
    }

    /**
     * Devuelve condición SQL para filtrar por unidad usando el valor normalizado y escapado.
     */
    private function unitCondition(?string $unit): string
    {
        $normalized = $this->normalizeUnit($unit);
        $escaped = $this->escapeUnit($normalized);
        return $escaped !== '' ? ' AND ' . self::COL_UNIDAD . ' = ' . $escaped : '';
    }

    public function buildUsuariosUnicosCte(string $endDate, ?string $unit = null): string
    {
        $end = $this->escapeDate($endDate);
        $unitCondition = $this->unitCondition($unit);

        return "WITH usuarios_unicos AS (
            SELECT
                *,
                ROW_NUMBER() OVER(
                    PARTITION BY id_cyaal_usr
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
     * @param int|null $limit Si es null o <= 0 no se aplica límite (producción). Para pruebas pasar ej. 100.
     */
    public function getDetails(string $endDate, string $type, ?string $unit = null, ?int $limit = null): array
    {
        $filter = self::STATUS_FILTERS[$type] ?? null;
        if ($filter === null) {
            return [];
        }

        $cte = $this->buildUsuariosUnicosCte($endDate, $unit);
        $limitClause = ($limit !== null && $limit > 0) ? " LIMIT {$limit}" : '';

        $sql = $cte . "
            SELECT *
            FROM usuarios_unicos
            WHERE orden = 1 AND ({$filter})" . $limitClause . "
        ";

        return DB::connection(self::CONNECTION)->select($sql);
    }

    /**
     * Gráfica de días suspendidos: MENOR, MODERADO, ELEVADO (según fch_cambio_estatus_cyaal_usr).
     */
    public function getSuspendedChart(string $endDate, ?string $unit = null): array
    {
        $end = $this->escapeDate($endDate);
        $unitCondition = $this->unitCondition($unit);

        $sql = "WITH usuarios_unicos AS (
            SELECT
                *,
                ROW_NUMBER() OVER(
                    PARTITION BY id_cyaal_usr
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
            'values' => array_map(fn($l) => (int) ($map[$l] ?? 0), $labels),
        ];
    }

    /**
     * Detalle de usuarios suspendidos por rango (MENOR, MODERADO o ELEVADO).
     * @param int|null $limit Si es null o <= 0 no se aplica límite. Para pruebas pasar ej. 100.
     */
    public function getSuspendedDetails(string $endDate, string $diasSuspendido, ?string $unit = null, ?int $limit = null): array
    {
        $valid = ['MENOR', 'MODERADO', 'ELEVADO'];
        if (! in_array($diasSuspendido, $valid, true)) {
            return [];
        }

        $end = $this->escapeDate($endDate);
        $unitCondition = $this->unitCondition($unit);
        $diasLiteral = "'" . str_replace("'", "''", $diasSuspendido) . "'";
        $limitClause = ($limit !== null && $limit > 0) ? " LIMIT {$limit}" : '';

        $sql = "WITH usuarios_unicos AS (
            SELECT
                *,
                ROW_NUMBER() OVER(
                    PARTITION BY id_cyaal_usr
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
        WHERE orden = 1 AND estatus_cyaal_usr = 'SUSPENDED' AND dias_suspendido = " . $diasLiteral . $limitClause . "
        ";

        return DB::connection(self::CONNECTION)->select($sql);
    }

    /**
     * Conteo de usuarios desactivados (evento user.lifecycle.deactivate) en una fecha.
     */
    public function getDeactivatedCount(string $date, ?string $unit = null): int
    {
        $dateLiteral = $this->escapeDate($date);
        $unitCondition = $this->unitCondition($unit);

        $sql = "SELECT COUNT(*) AS conteo FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.deactivate'
              AND resultado_salida = 'SUCCESS'
              AND CAST(fch_publicacion_utc AS DATE) = " . $dateLiteral . $unitCondition;

        $row = DB::connection(self::CONNECTION)->selectOne($sql);

        return (int) ($row->conteo ?? 0);
    }

    /**
     * Detalle de usuarios desactivados en una fecha.
     * @param int|null $limit Si es null o <= 0 no se aplica límite. Para pruebas pasar ej. 100.
     */
    public function getDeactivatedDetails(string $date, ?string $unit = null, ?int $limit = null): array
    {
        $dateLiteral = $this->escapeDate($date);
        $unitCondition = $this->unitCondition($unit);
        $limitClause = ($limit !== null && $limit > 0) ? " LIMIT {$limit}" : '';

        $sql = "SELECT * FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.deactivate'
              AND resultado_salida = 'SUCCESS'
              AND CAST(fch_publicacion_utc AS DATE) = " . $dateLiteral . $unitCondition . $limitClause;

        return DB::connection(self::CONNECTION)->select($sql);
    }

    public function getUsersAddCounts(string $endDate, ?string $unit = null): stdClass
    {
        $firstDayOfMonth = date('Y-m-01', strtotime($endDate));
        $end = $this->escapeDate($endDate);
        $first = $this->escapeDate($firstDayOfMonth);
        $unitCondition = $this->unitCondition($unit);

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
     * @param int|null $limit Si es null o <= 0 no se aplica límite. Para pruebas pasar ej. 100.
     */
    public function getUsersAddDetails(string $endDate, string $type, ?string $unit = null, ?int $limit = null): array
    {
        $firstDayOfMonth = date('Y-m-01', strtotime($endDate));
        $end = $this->escapeDate($endDate);
        $first = $this->escapeDate($firstDayOfMonth);
        $unitCondition = $this->unitCondition($unit);
        $limitClause = ($limit !== null && $limit > 0) ? " LIMIT {$limit}" : '';

        $dateCondition = match ($type) {
            'dia_alta'   => 'AND CAST(fch_publicacion_utc AS DATE) = ' . $end,
            'mes_alta'   => 'AND CAST(fch_publicacion_utc AS DATE) BETWEEN ' . $first . ' AND ' . $end,
            'total_alta' => '',
            default      => '',
        };

        $sql = "SELECT * FROM " . self::TABLE_LOGS . "
            WHERE tipo_evento = 'user.lifecycle.create'
              AND resultado_salida = 'SUCCESS'
              {$dateCondition}" . $unitCondition . $limitClause;

        return DB::connection(self::CONNECTION)->select($sql);
    }

    public function getWeeklyTrend(string $endDate, ?string $unit = null): array
    {
        $end = $this->escapeDate($endDate);
        $unitCondition = $this->unitCondition($unit);

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

    public function getYearlyTrend(string $endDate, ?string $unit = null): array
    {
        $end = $this->escapeDate($endDate);
        $unitCondition = $this->unitCondition($unit);

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

    /**
     * Mapa de unidades: clave (para request/URL) => valor en BD.
     * Útil para el frontend o para validar qué unidades están permitidas.
     */
    public static function getUnitMap(): array
    {
        return self::UNIT_MAP;
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
