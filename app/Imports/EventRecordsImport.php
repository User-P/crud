<?php

namespace App\Imports;

use App\Models\EventRecord;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;


use Throwable;

class EventRecordsImport extends DefaultValueBinder implements OnEachRow, WithHeadingRow, SkipsOnFailure, SkipsEmptyRows, WithCustomValueBinder
{
    use Importable;
    use SkipsFailures;

    private int $created = 0;
    private int $duplicates = 0;

    private string $dateFormat = 'Y/m/d H:i:s';
    private string $cell = 'A';
    private int $currentRow = 1;


    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === $this->cell) {
            if (is_numeric($value)) {
                $date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
                $cell->setValue($date->format($this->dateFormat));
                return true;
            } else {
                $date = Carbon::parse($value);
                $cell->setValue($date->format($this->dateFormat));
                return true;
            }
        }

        return parent::bindValue($cell, $value);
    }

    public function onRow(Row $row): void
    {
        $this->currentRow = $row->getIndex();
        $raw = $row->toArray();
        try {
            $data = $this->prepareRow($raw);
        } catch (Throwable $exception) {
            $this->onFailure(new Failure($this->currentRow, 'fecha', [$exception->getMessage()], $raw));
            return;
        }


        if (in_array(null, $data, true)) {
            $this->onFailure(new Failure($this->currentRow, 'general', ['Datos incompletos'], $raw));
            return;
        }

        if ($this->recordExists($data)) {
            return;
        }

        EventRecord::insert($data);
        $this->created++;
    }

    private function recordExists(array $data): bool
    {
        $exists = EventRecord::where('fecha', $data['fecha'])
            ->where('que_se_encontro', $data['que_se_encontro'])
            ->where('en_donde', $data['en_donde'])
            ->where('actividad_relevante', $data['actividad_relevante'])
            ->where('tipo_de_actividad', $data['tipo_de_actividad'])
            ->where('es_actividad_anomala', $data['es_actividad_anomala'])
            ->exists();

        if ($exists) {
            $this->duplicates++;
            return $exists;
        }

        return $exists;
    }

    public function getSummary(): array
    {
        $skipped = $this->failures()->count();

        return [
            'inserted' => $this->created,
            'errors' => $skipped,
            'duplicates' => $this->duplicates,
        ];
    }

    public function getAllFailures(): array
    {
        return array_merge($this->failures()->toArray());
    }

    private function prepareRow(array $row): array
    {
        $date = $this->validateFormateDate((string) Arr::get($row, 'fecha'));

        // Helper para limpiar y validar campos de texto
        $cleanField = function ($value) {
            $cleaned = trim((string) $value);
            return $cleaned === '' ? null : $cleaned;
        };

        return [
            'fecha' => $date,
            'que_se_encontro' => $cleanField(Arr::get($row, 'que_se_encontro')),
            'en_donde' => $cleanField(Arr::get($row, 'en_donde')),
            'actividad_relevante' => $cleanField(Arr::get($row, 'actividad_relevante')),
            'tipo_de_actividad' => $cleanField(Arr::get($row, 'tipo_de_actividad')),
            'es_actividad_anomala' => (bool) Arr::get($row, 'es_actividad_anomala'),
        ];
    }

    private function validateFormateDate(string $date): string
    {
        // Limpiar la fecha: eliminar espacios múltiples y formato AM/PM
        $cleanDate = trim($date);
        // Reemplazar espacios múltiples por un solo espacio
        $cleanDate = preg_replace('/\s+/', ' ', $cleanDate);
        // Eliminar a.m., p.m., AM, PM (con o sin puntos)
        $cleanDate = preg_replace('/\s*(a\.m\.|p\.m\.|am|pm)\.?/i', '', $cleanDate);

        // Intentar parsear la fecha limpia con Carbon
        try {
            // Primero intentar con el formato esperado
            $d = Carbon::createFromFormat($this->dateFormat, $cleanDate);
            return $d->format($this->dateFormat);
        } catch (Throwable $exception) {
            // Si falla, intentar parsear con formato flexible
            try {
                // Carbon::parse puede manejar múltiples formatos automáticamente
                $d = Carbon::parse($cleanDate);
                return $d->format($this->dateFormat);
            } catch (Throwable $e) {
                throw new \Exception("La fecha '$date' no pudo ser procesada. Formato esperado: {$this->dateFormat}");
            }
        }
    }
}
