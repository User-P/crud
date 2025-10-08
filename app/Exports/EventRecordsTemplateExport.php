<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventRecordsTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    private const SOURCE_OPTIONS = [
        'si',
        'no',
    ];

    public function collection(): Collection
    {
        return collect([[]]);
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

    public function registerEvents(): array {

        return [
            AfterSheet::class => function (AfterSheet $event) {

                $dropdown_cell = 'A';
                $dropdown_range = 'A2:A' . ($event->sheet->getHighestRow() + 1);
                $this->getDataValidation($event, $dropdown_cell, self::SOURCE_OPTIONS, $dropdown_range, false);

                $dropdown_cell = 'B';
                $dropdown_range = 'B2:B' . ($event->sheet->getHighestRow() + 1);
                $categorySheet = new CategorySheet();
                $this->getDataValidation(
                    $event,
                    $dropdown_cell,
                    CategorySheet::CATEGORIES,
                    $dropdown_range,
                    true,
                    $categorySheet->title()
                );
            },
        ];
    }

    public function getDataValidation(AfterSheet $event, string $dropdown_cell, array $options, string $dropdown_range, bool $type, string $nameSheet = null): DataValidation
    {
        $validation = $event->sheet->getCell("{$dropdown_cell}7")->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Error!');
        $validation->setError('El valor especificado no se encuentra en la lista, debes seleccionar un valor de la lista.');
        $validation->setPromptTitle('Selecciona un valor de la lista');
        $validation->setPrompt('Por favor selecciona un valor de la lista.');
        if (!$type) {
            $validation->setFormula1('"' . implode(',', $options) . '"');
        } else {
            $count = count($options);
            $sheetName = str_replace("'", "''", (string) $nameSheet);
            $validation->setFormula1(sprintf("'%s'!\$A\$1:\$A\$%d", $sheetName, $count));
        }

        $event->sheet->setDataValidation($dropdown_range, $validation);
        return $validation;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
