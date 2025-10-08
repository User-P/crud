<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategorySheet implements FromCollection, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect([
            ['category 1'],
            ['category 2'],
            ['category 3'],
            ['category 4'],
            ['category 5'],
            ['categoria con acentos áéíóú 1'],
            ['categoria con acentos áéíóú 2'],
            ['categoria con acentos áéíóú 3'],
            ['categoria con acentos áéíóú 4'],
            ['categoria con acentos áéíóú 5'],
            ['texto grande para hacer pruebas de como se ve en el excel 1'],
            ['texto grande para hacer pruebas de como se ve en el excel 2'],
            ['texto grande para hacer pruebas de como se ve en el excel 3'],
            ['texto grande para hacer pruebas de como se ve en el excel 4'],
            ['texto grande para hacer pruebas de como se ve en el excel 5'],
        ]);
    }

    public function title(): string
    {
        return 'categories';
    }
}
