<?php

namespace App\Exports\Monitoring;

use App\Models\TblResultadoAccionable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CollaborationSheet implements FromView, ShouldAutoSize, WithStyles, WithTitle
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

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->mergeCells('A1:I1');

        $sheet->getStyle('A2')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->mergeCells('A2:I2');

        $sheet->getStyle('A3:I3')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
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

        $sheet->getRowDimension(3)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(40);
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

        $headers = ['Semana', 'Fecha Solicitud', 'Acción', 'Respuesta', 'Número de empleado'];

        $registers = $resultados->map(function (TblResultadoAccionable $r) {
            return [
                'Semana' => $r->fch_solicitud ? (int) $r->fch_solicitud->format('W') : '',
                'Fecha Solicitud' => $r->fch_solicitud?->format('d-m-Y') ?? '',
                'Acción' => $r->accion?->descripcion ?? '',
                'Respuesta' => $r->accionRespuesta?->descripcion ?? '',
                'Número de empleado' => $r->alertaSeguimiento?->cve_empleado ?? '',
            ];
        })->toArray();

        return view('exports.actionables', [
            'sheetTitle' => $this->type['tipo_accionable'] ?? 'Accionables',
            'directivos' => $this->directivos,
            'headers' => $headers,
            'registers' => $registers,
        ]);
    }
}
