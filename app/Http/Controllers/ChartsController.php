<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Services\MapeoFuentes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChartsController extends Controller
{
    protected $mapeoFuentes;

    // public function __construct()
    // {
    //     $this->mapeoFuentes = new MapeoFuentes;
    // }

    public function __invoke()
    {
        return Inertia::render('Charts/Index', [
            'pieData' => [
                ['value' => 1048, 'name' => 'Búsquedas'],
                ['value' => 735, 'name' => 'Redes'],
                ['value' => 580, 'name' => 'Email'],
                ['value' => 484, 'name' => 'Referidos'],
                ['value' => 300, 'name' => 'Directo'],
            ],
            'test' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            'lineSeries' => [
                ['name' => 'Tráfico web', 'data' => [120, 132, 101, 134, 90, 230, 210, 240, 260, 230, 250, 270]],
                ['name' => 'App móvil', 'data' => [80, 110, 95, 120, 130, 150, 170, 180, 200, 210, 190, 220]],
            ],
            'barCategories' => ['Q1', 'Q2', 'Q3', 'Q4'],
            'barSeries' => [
                ['name' => 'Ventas', 'data' => [320, 432, 501, 434]],
                ['name' => 'Objetivo', 'data' => [380, 410, 460, 480]],
            ],
            'radarIndicators' => [
                ['name' => 'Disponibilidad', 'max' => 100],
                ['name' => 'Latencia', 'max' => 100],
                ['name' => 'Seguridad', 'max' => 100],
                ['name' => 'Satisfacción', 'max' => 100],
                ['name' => 'Entrega', 'max' => 100],
            ],
            'radarData' => [
                ['name' => 'Planta Norte', 'value' => [92, 85, 78, 88, 82]],
                ['name' => 'Planta Sur', 'value' => [88, 80, 90, 84, 79]],
            ],
            'gaugeValue' => 94.5,
        ]);
    }

    public function usageAlerts(Request $request)
    {
        $cve   = $request->cve;
        $start = $request->start;
        $end   = $request->end;

        $rawData    = [];
        $typeGroups = [];
        foreach ($request->types as $typeWithGroup) {
            $type  = $typeWithGroup['name'];
            $group = $typeWithGroup['group'];
            $typeGroups[$type] = $group;

            $dlp = $this->resolveFieldsDlp($type);
            if (!$dlp) {
                $rawData[$type] = $this->getDataMOTM($cve, $start, $end, $type);
            } else {
                $rawData[$type] = $this->getDataDlp($cve, $start, $end, $type, $dlp);
            }
        }

        $categories = collect($rawData)
            ->flatMap(fn($items) => $items->keys())
            ->unique()
            ->sort()
            ->values()
            ->all();

        $totalsByCategory = [];
        foreach ($categories as $date) {
            $sum = 0;
            foreach ($rawData as $items) {
                $sum += $items[$date]['value'] ?? 0;
            }
            $totalsByCategory[$date] = $sum;
        }
        $categories = array_values(array_filter(
            $categories,
            fn($date) => ($totalsByCategory[$date] ?? 0) > 0
        ));
        $series = [];
        foreach ($rawData as $type => $items) {
            $data = collect($categories)
                ->map(fn($date) => $items[$date]['value'] ?? 0)
                ->values()
                ->all();

            $series[] = [
                'name' => $type,
                'data' => $data,
            ];
        }

        $series = array_values(array_filter($series, function ($s) {
            if (empty($s['data'])) return false;
            foreach ($s['data'] as $v) {
                if ((float)$v !== 0.0) return true;
            }
            return false;
        }));

        $merged = [];
        foreach ($rawData as $type => $items) {
            $group = $typeGroups[$type] ?? null;
            foreach ($items as $payload) {
                $detailsByComp = $payload['details'] ?? [];
                foreach ($detailsByComp as $behavior => $rows) {
                    if (!isset($merged[$behavior])) {
                        $merged[$behavior] = ['group' => $group, 'rows' => []];
                    } else {
                        if (!isset($merged[$behavior]['group']) || $merged[$behavior]['group'] === null) {
                            $merged[$behavior]['group'] = $group;
                        }
                    }
                    foreach ($rows as $r) {
                        if (is_array($r)) {
                            $r['group'] = $group;
                        } elseif (is_object($r)) {
                            $r->group = $group;
                        }
                        $merged[$behavior]['rows'][] = $r;
                    }
                }
            }
        }

        $details = collect($merged)
            ->map(function (array $entry, string $behavior) {
                $notation = $this->resolveNotation($behavior);
                $tipo = $entry['group'] ?? $notation['tipo_wba'];

                return [
                    'uuid'       => Str::uuid(),
                    'name'       => $notation['title'],
                    'subtitulo'  => $notation['subtitle'],
                    'tipo'       => $tipo,                    // <-- aquí va el group
                    'cantidad'   => count($entry['rows']),
                    'details'    => array_values($entry['rows']), // cada fila ya trae 'group'
                ];
            })
            ->values();

        return [
            'categories' => $categories,
            'series'     => $series,
            'details'    => $details,
        ];
    }

    public function getDataMOTM(string $cve, string $start, string $end, string $type)
    {
        $fuente    = $this->mapeoFuentes->setearFuente($type);
        $table     = $this->mapeoFuentes->configuracionFuenteOrigen('DB_TABLE_' . $fuente . '_' . $type);

        $dateField = $this->dateFieldFor($type, $table);

        $query = DB::connection(strtolower($fuente))->select(
            "SELECT * FROM {$table}
             WHERE {$dateField} BETWEEN '{$start}' AND '{$end}'
            --  AND cve_empleado = {$cve}
            limit 10
             "
        );

        return collect($query)
            ->groupBy($dateField)
            ->map(function ($itemsByDate) use ($type) {
                $total = $itemsByDate->count();
                $detailsByComp = $itemsByDate
                    ->groupBy($this->resolveGroupField($type))
                    ->map(fn($rows) => $rows->values());
                return [
                    'value'   => $total,
                    'details' => $detailsByComp,
                ];
            });
    }

    private function getDataDlp(string $cve, string $start, string $end, string $type, array $dlp)
    {
        $table = $this->mapeoFuentes->configuracionFuenteOrigen($dlp['table']);

        $whereExtra = '';
        if (isset($dlp['values'])) {
            if (is_array($dlp['values'])) {
                $inValues = implode("','", $dlp['values']);
                $whereExtra = " AND {$dlp['field']} IN ('{$inValues}')";
            } elseif (is_string($dlp['values'])) {
                $whereExtra = " AND {$dlp['field']} LIKE '{$dlp['values']}'";
            }
        }

        if (isset($dlp['query_type']))
            if ($dlp['query_type'] == 'not like') {
                $whereExtra = " AND  {$dlp['field']}  NOT LIKE '{$dlp['value']}'";
            }

        $query = DB::connection(strtolower($dlp['connection']))->select(
            "SELECT * FROM {$table}
         WHERE {$dlp['date']} BETWEEN '{$start}' AND '{$end}'
         {$whereExtra}
            --  AND cve_empleado = {$cve}
            limit 10
             "
        );

        return collect($query)
            ->groupBy($dlp['date'])
            ->map(function ($itemsByDate) use ($dlp) {
                $total = $itemsByDate->count();
                $detailsByComp = collect([
                    $dlp['title'] => $itemsByDate->values()
                ]);
                return [
                    'value'   => $total,
                    'details' => $detailsByComp,
                ];
            });
    }

    public function resolveNotation(string $notacion): array
    {
        $response = DB::table("CAT_NOTACIONES_WBA")
            ->select(['titulo', 'tipo_wba', 'subtitulo'])
            ->where("notacion", $notacion)->first();

        return  [
            'title' => $response->titulo ?? $notacion,
            'tipo_wba'   => $response->tipo_wba ?? 'N/A',
            'subtitle' => $response->subtitulo ?? 'No se encuentra la clave en el catalogo'
        ];
    }

    private function dateFieldFor(string $type, $table): string
    {
        return  match ($type) {
            'WBA_TRM', 'TRM' => 'fch_recepcion',
            'KEYWORD', 'EVENTO', 'COMPORTAMIENTO' => 'fch_alerta',
            default => throw new \RuntimeException("No hay mapeo de campo fecha para la tabla: {$table}")
        };
    }

    private function resolveGroupField(string $type): string
    {
        return  match ($type) {
            'WBA_TRM' => 'nm_comportamiento',
            'KEYWORD', 'EVENTO', 'COMPORTAMIENTO' => 'nm_reglas',
            default => throw new \RuntimeException("No hay grupo para: {$type}")
        };
    }

    private function resolveFieldsDlp($tipo)
    {
        switch ($tipo) {
            case 'SEVERIDAD ALTA':
                return [
                    'title' => 'Incidencias en políticas severidad alta.',
                    'subtitle' => 'Empleados con más de 20 incidencias en un día de políticas con severidad High que no se bloquearon (filtros passed y none).',
                    'date' => 'fch_incidente',
                    'field' => 'nm_politica',
                    'table' => "DB_TABLE_VERTICA_DLP",
                    'connection' => "VERTICA",
                    'values' => [
                        '1. PDFC / A / CI-AR / A',
                        '1. PDFC / A / CI-AR / B',
                        '2. PDPC / A / CI-CF / A',
                        '2. PDPC / A / CI-CF / B',
                        '4. PB / A / CI-AR / A',
                        '4. PB / A / CI-AR / B'
                    ]
                ];
            case 'IP ORIGEN DIFERENTE':
                return [
                    'title' => 'IP máquina origen diferente a 10.XX.XX.XX',
                    // 'subtitle' => '',
                    'date' => 'fch_incidente',
                    'table' => "DB_TABLE_VERTICA_DLP",
                    'connection' => "VERTICA",
                    'field' => 'ip_maquina_origen',
                    'query_type' => "not like",
                    "value" => '10.%'
                ];
            case 'PROTOCOLO EMAIL':
                return [
                    'title' => 'Eventos del protocolo email',
                    // 'subtitle' => '',
                    'date' => 'fch_incidente',
                    'table' => "DB_TABLE_VERTICA_DLP",
                    'connection' => "VERTICA",
                    'field' => 'desc_protocolo_dispositivo',
                    "values" => 'Endpoint Email/SMTP'
                ];
            case 'PROTECCIÓN CÓDIGO FUENTE':
                return [
                    'title' => 'Incidencias en políticas Protección Código Fuente',
                    'subtitle' => 'Empleados que infrinjen la política de “Protección de código fuente” (3. PCF / M / CI-CF / A ) con más de 20 registros en un día sin considerar el protocolo DAR Connector.',
                    'date' => 'fch_incidente',
                    'table' => "DB_TABLE_VERTICA_DLP",
                    'connection' => "VERTICA",
                    'field' => 'nm_politica',
                    "value" => '3. PCF / M / CI-CF / A'
                ];
            case 'POLÍTICA DE MENSAJERÍA':
                return [
                    'title' => 'Incidencias en políticas de Mensajerías',
                    'date' => 'fch_incidente',
                    'table' => "DB_TABLE_VERTICA_DLP",
                    'connection' => "VERTICA",
                    'field' => 'nm_politica',
                    "value" => '7. MEN / M / CI-CF / A'
                ];
            case 'POLÍTICA DE USO DE USB':
                return [
                    'title' => 'Incidencias en políticas de Uso USB',
                    'date' => 'fch_incidente',
                    'table' => "DB_TABLE_VERTICA_DLP",
                    'connection' => "VERTICA",
                    'field' => 'nm_politica',
                    "value" => 'DSI: Masivos USB'
                ];
            case 'POLÍTICA DE DATOS PERSONALES':
                return [
                    'title' => 'Incidencias en políticas Datos Personales Clientes',
                    'date' => 'fch_incidente',
                    'table' => "DB_TABLE_VERTICA_DLP",
                    'connection' => "VERTICA",
                    'field' => 'nm_politica',
                    "value" => '2. PDPC / A / CI-CF / A'
                ];
            case 'POLÍTICA DE DATOS PERSONALES Y TDC':
                return [
                    'title' => 'Incidencias en políticas Datos Personales Clientes + Tarjetas Crédito',
                    'date' => 'fch_incidente',
                    'table' => "DB_TABLE_VERTICA_DLP",
                    'connection' => "VERTICA",
                    'field' => 'nm_politica',
                    "value" => 'DSI: PCI - V3 - Auditoria'
                ];
            case 'POLÍTICA BIN BANCO AZTECA':
                return [
                    'title' => 'Incidencias en políticas BIN Banco Azteca',
                    'date' => 'fch_incidente',
                    'table' => "DB_TABLE_VERTICA_DLP",
                    'connection' => "VERTICA",
                    'field' => 'nm_politica',
                    "value" => 'DSI: BAZ_BIN2'
                ];
            case 'POLÍTICA CUENTAS CLABE O #TDC':
                return [
                    'title' => 'Incidencias en políticas Ctas CLABE o # Tarjeta',
                    'date' => 'fch_incidente',
                    'table' => "DB_TABLE_VERTICA_DLP",
                    'connection' => "VERTICA",
                    'field' => 'nm_politica',
                    'values' => [
                        'DSI: PCI - V3 - Auditoria',
                        'DSI: PCI Guatemala - Auditoria',
                        'DSI: PCI Honduras',
                        'DSI: PCI',
                        'PCI',
                        'DSI: PCI - Discovery'
                    ]
                ];
            case 'POLÍTICA LB BLOQUEO':
                return [
                    'title' => 'Incidencias en políticas LB - Bloqueo',
                    'date' => 'fch_incidente',
                    'table' => "DB_TABLE_VERTICA_DLP",
                    'connection' => "VERTICA",
                    'field' => 'nm_politica',
                    'value' => '%DSI: BAZ LB%'
                ];

            case 'ALERTA ONEDRIVE':
                return [
                    'title' => 'Manejo Archivos en One Drive',
                    'date' => 'fch_envio_documento',
                    'table' => "DB_TABLE_VERTICA_MCO",
                    'connection' => "VERTICA",
                ];
            case 'BORRAR ARCHIVOS':
                return [
                    'title' => 'Empleados que borran archivos en un día',
                    'date' => 'fch_recepcion',
                    'table' => "DB_TABLE_VERTICA_TRM",
                    'connection' => "VERTICA",
                ];
            case 'REMOVABLE':
                return [
                    'title' => 'Endpoint Removable',
                    'date' => 'fch_evento',
                    'table' => "DB_TABLE_CLOUDERA_RESUMEN",
                    'connection' => "CLOUDERA",
                ];
            case 'TECLEO':
                return [
                    'title' => 'Modelo del manejo de transacciones en Tecleo Financiero',
                    'subtitle' => 'Muestra los empleados que presentaron un manejo atípico de transacciones de clientes en Tecleo Financiero Alnova, considerando en el score de riesgo: tipo de transacción, volúmen y recurrencia.',
                    'table' => "DB_TABLE_CLOUDERA_TECLEO",
                    'connection' => "CLOUDERA",
                    'date' => 'fch_carga',
                    'field' => 'codigo_transaccion',
                    // "values" => ''
                ];
            case 'RESUMEN':
                return [
                    'title' => 'Modelo de Infractores DLP',
                    'subtitle' => 'Muestra los infractores que incumplieron reglas DLP, con mayor score de riesgo, considerando: la criticidad de la información, volúmen y recurrencia de eventos, respecto a sí mismo y al área de pertenencia.',
                    'table' => "DB_TABLE_CLOUDERA_RESUMEN",
                    'connection' => "CLOUDERA",
                    'date' => 'fch_evento',
                    'field' => 'frecuencia_evento',
                    // "values" => ''
                ];
            case 'RESUMEN_TI':
                return [
                    'title' => 'Modelo de Detección Infractores DLP-INFINITE',
                    'subtitle' => 'Muestra el número de infractores de reglas DLP, con manejo de información de Tarjetas Infinite, distribuidos por el nivel de riesgo que representan, considerando la criticidad de la información, volumen y recurrencia de eventos realizados, respecto a sí mismos y al área de pertenencia.',
                    'table' => "DB_TABLE_CLOUDERA_RESUMEN_TI",
                    'connection' => "CLOUDERA",
                    'date' => 'fch_evento',
                    'field' => 'frecuencia_evento',
                    // "values" => ''
                ];
            case 'BUSQUEDA_EMPLEO':
                return [
                    'title' => 'Modelo Búsqueda de Empleo',
                    'subtitle' => 'Muestra a los empleados que tienen mayor score de riesgo con posibilidad de buscar y cambiar de empleo y que pudieran estar en escenarios de fuga o robo de información u otros eventos de afectación a la organización.',
                    'table' => "DB_TABLE_VERTICA_BUSQUEDA_EMPLEO",
                    'connection' => "VERTICA",
                    'date' => 'fch_proceso_fecha',
                    'field' => 'fch_maxima_score',
                    // "values" => ''
                ];
            default:
                return null;
        }
    }
}
