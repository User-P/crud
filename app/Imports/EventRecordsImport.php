<?php

namespace App\Imports;

use App\Models\EventRecord;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class EventRecordsImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable;
    use SkipsFailures;

    private int $created = 0;

    public function onRow(Row $row): void
    {
        $raw = $row->toArray();

        try {
            $data = $this->prepareRow($raw);
        } catch (Throwable $exception) {
            $this->onFailure(new Failure(
                $row->getIndex(),
                'recorded_at',
                ['El campo recorded_at debe contener una fecha válida.'],
                $raw
            ));

            return;
        }

        EventRecord::create($data);
        $this->created++;
    }

    public function rules(): array
    {
        return [
            '*.source' => ['required', 'string', 'max:255'],
            '*.category' => ['required', 'string', 'max:120'],
            '*.description' => ['required', 'string'],
            '*.notes' => ['nullable', 'string'],
            '*.recorded_at' => [
                'required',
                'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', // Valida el formato YYYY-MM-DD HH:MM:SS
            ],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.source.required' => 'Debes indicar la fuente.',
            '*.category.required' => 'Debes indicar la categoría.',
            '*.description.required' => 'La descripción es obligatoria.',
            '*.recorded_at.required' => 'El campo recorded_at es obligatorio.',
            '*.recorded_at.regex' => 'El campo recorded_at debe estar en el formato "YYYY-MM-DD HH:MM:SS" e incluir una hora válida.',
        ];
    }

    public function getSummary(): array
    {
        $skipped = $this->failures()->count();

        return [
            'inserted' => $this->created,
            'skipped' => $skipped,
            'processed' => $this->created + $skipped,
        ];
    }

    public function getFormattedFailures(): array
    {
        return $this->failures()
            ->map(static fn(Failure $failure): array => [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ])
            ->values()
            ->toArray();
    }

    private function prepareRow(array $row): array
    {
        $recordedAt = $this->parseTimestamp(Arr::get($row, 'recorded_at'));

        return [
            'source' => trim((string) Arr::get($row, 'source')),
            'category' => trim((string) Arr::get($row, 'category')),
            'description' => trim((string) Arr::get($row, 'description')),
            'notes' => $this->normalizeNotes(Arr::get($row, 'notes')),
            'recorded_at' => $recordedAt,
        ];
    }

    private function normalizeNotes(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $notes = trim((string) $value);

        return $notes === '' ? null : $notes;
    }

    private function parseTimestamp(mixed $value): string
    {
        if ($value === null || $value === '') {
            throw new \InvalidArgumentException('El campo recorded_at no puede estar vacío.');
        }

        // Verificar si el formato es correcto
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string) $value)) {
            throw new \InvalidArgumentException('El campo recorded_at debe estar en el formato "YYYY-MM-DD HH:MM:SS".');
        }

        try {
            return Carbon::parse((string) $value)->toDateTimeString();
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('El campo recorded_at contiene una fecha u hora inválida.');
        }
    }
}