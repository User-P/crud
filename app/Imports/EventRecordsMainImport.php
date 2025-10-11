<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EventRecordsMainImport implements WithMultipleSheets
{
  private EventRecordsImport $sheetImport;

  public function __construct()
  {
    $this->sheetImport = new EventRecordsImport();
  }
  
  public function sheets(): array
  {
    return [
      0 => $this->sheetImport,
    ];
  }

  public function getSummary(): array
  {
    return $this->sheetImport->getSummary();
  }


    public function failures()
    {
        return $this->sheetImport->failures();
    }
}
