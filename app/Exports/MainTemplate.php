<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MainTemplate implements WithMultipleSheets
{

    public function sheets(): array
    {
        return [
            new EventRecordsTemplateExport(),
            new CategorySheet(),
        ];
    }
}
