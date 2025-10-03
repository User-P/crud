<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventRecordsTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection(): Collection
    {
        return collect([
            [
                'Sensor A',
                'temperature',
                'Describe el evento aquí',
                'Notas opcionales',
                '2025-10-03 09:15:00',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'source',
            'category',
            'description',
            'notes',
            'recorded_at',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 18,
            'C' => 45,
            'D' => 28,
            'E' => 24,
        ];
    }
}
