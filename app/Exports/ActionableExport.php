<?php

namespace App\Exports;

use App\Models\CatDirectivo;
use App\Models\CatTiposAccionables;
use App\Models\TblResultadoAccionable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ActionableExport implements WithMultipleSheets, ShouldAutoSize
{
    protected $start_date;
    protected $end_date;

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function sheets(): array
    {
        $sheets = [];
        $directivos = CatDirectivo::select('nm_directivo', 'siglas_directivo')->get();
        $types = CatTiposAccionables::select('uuid_tipo_accionable', 'tipo_accionable')->get()->toArray();

        foreach ($types as $type) {

            $sheets[] = new CollaborationSheet($type, $this->start_date, $this->end_date, $directivos);
        }
        return $sheets;
    }
}
