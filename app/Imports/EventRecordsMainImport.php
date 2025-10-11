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

  /**
   * Define qué hojas se deben importar
   * Solo importa la primera hoja (índice 0)
   */
  public function sheets(): array
  {
    return [
      0 => $this->sheetImport, // Solo procesa la primera hoja (Sheet1)
    ];
  }

  /**
   * Obtiene el resumen de la importación
   */
  public function getSummary(): array
  {
    return $this->sheetImport->getSummary();
  }

    /**
     * Obtiene todos los fallos
     */

    public function failures()
    {
        return $this->sheetImport->failures();
    }
}
