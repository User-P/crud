<?php

namespace App\Exports\Monitoring;

use App\Models\TblResultadoAccionable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CollaborationSheet implements FromView, ShouldAutoSize,  WithStyles
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


    public function view(): View
    {

        // traen relacion id_alerta_seguimiento en ella esta cve_empleado
        $resultados = collect(TblResultadoAccionable::query()
            ->whereBetween('fch_solicitud', [$this->start_date, $this->end_date])
            ->where('uuid_tipo_accionable', $this->type['uuid_tipo_accionable'])
            ->orderBy('fch_solicitud')
            ->get())->groupBy('id_alerta_seguimiento');

        dd($resultados);

        // return view('exports.actionables', [
        //     'directivos' => $this->directivos,
        //     'headers' => $headers,
        //     'registers' => $registers
        // ]);
    }
}
