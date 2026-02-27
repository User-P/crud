<?php

namespace App\Exports\Monitoring;

use App\Models\TblResultadoAccionable;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CollaborationSheet implements FromView, ShouldAutoSize, WithTitle, WithEvents
{

    protected $start_date;
    protected $end_date;
    protected $directivos;
    protected $type;

    public function __construct(array $type, string $start_date, string $end_date, $directivos)
    {
        $this->type = $type;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->directivos = $directivos;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = $sheet->getHighestColumn();
                $rangeHeader = 'A1:' . $lastCol . '1';
                $rangeDirectivos = 'A2:' . $lastCol . '2';
                $rangeHeaders = 'A3:' . $lastCol . '3';

                $sheet->mergeCells($rangeHeader);
                $sheet->mergeCells($rangeDirectivos);
                $sheet->getStyle($rangeHeader)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
                $sheet->getStyle($rangeDirectivos)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
                $sheet->getStyle($rangeHeaders)->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => false,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'D9D9D9'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(40);
                $sheet->getRowDimension(3)->setRowHeight(22);
            },
        ];
    }

    public function title(): string
    {
        return $this->type['tipo_accionable'] ?? 'Hoja';
    }

    public function view(): View
    {
        $resultados = TblResultadoAccionable::query()
            ->with(['accion', 'accionRespuesta', 'alertaSeguimiento'])
            ->whereBetween('fch_solicitud', [$this->start_date, $this->end_date])
            ->where('uuid_tipo_accionable', $this->type['uuid_tipo_accionable'])
            ->orderBy('fch_solicitud')
            ->get();

        // Columnas fijas + una columna por cada acción (el header es la acción, la celda es la respuesta)
        $actionHeaders = $resultados->pluck('accion.descripcion')->filter()->unique()->values()->toArray();
        $headers = array_merge(['Semana', 'Fecha Solicitud', 'Número de empleado'], $actionHeaders);

        // Agrupar por alerta de seguimiento: un registro por empleado/caso
        $grouped = $resultados->groupBy('id_alerta_seguimiento');
        $registers = [];

        foreach ($grouped as $idAlerta => $items) {
            $first = $items->first();
            $fchSolicitud = $first->fch_solicitud
                ? (is_string($first->fch_solicitud) ? Carbon::parse($first->fch_solicitud) : $first->fch_solicitud)
                : null;

            $row = [
                'Semana' => $fchSolicitud ? (int) $fchSolicitud->format('W') : '',
                'Fecha Solicitud' => $fchSolicitud ? $fchSolicitud->format('d-m-Y') : '',
                'Número de empleado' => $first->alertaSeguimiento?->cve_empleado ?? '',
            ];

            // Para cada acción, poner la respuesta en su columna
            $accionARespuesta = $items->keyBy(fn (TblResultadoAccionable $r) => $r->accion?->descripcion ?? '');
            foreach ($actionHeaders as $actionHeader) {
                $row[$actionHeader] = $accionARespuesta->get($actionHeader)?->accionRespuesta?->descripcion ?? '';
            }

            $registers[] = $row;
        }

        return view('exports.actionables', [
            'sheetTitle' => $this->type['tipo_accionable'] ?? 'Accionables',
            'directivos' => $this->directivos,
            'headers' => $headers,
            'registers' => $registers,
        ]);
    }
}
