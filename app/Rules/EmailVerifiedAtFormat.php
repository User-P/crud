<?php

namespace App\Rules;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EmailVerifiedAtFormat implements ValidationRule
{
    public const HUMAN_READABLE_EXPECTATION = 'DD/MM/YYYY HH:mm:ss (24 horas)';

    // Formato único aceptado para la fecha manual
    private const ACCEPTED_FORMAT = 'd/m/Y H:i:s';

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        try {
            self::normalize($value);
        } catch (InvalidArgumentException $exception) {
            $fail(
                __('El campo :attribute debe tener el formato exacto :format (ejemplo: 31/12/2025 23:59:59) o estar vacío.', [
                    'attribute' => $attribute,
                    'format' => self::HUMAN_READABLE_EXPECTATION,
                ])
            );
        }
    }

    public static function normalize(mixed $value): ?CarbonImmutable
    {
        if ($value === null) return null;
        if (is_string($value)) $value = trim($value);
        if ($value === '') return null;
        if (is_numeric($value)) {
            try {
                $dateTime = ExcelDate::excelToDateTimeObject((float) $value);
                return CarbonImmutable::instance($dateTime)->setTimezone(config('app.timezone'));
            } catch (\Throwable $exception) {
                throw new InvalidArgumentException('El valor numérico no puede convertirse en fecha/hora válida.', 0, $exception);
            }
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException('El valor debe ser una cadena compatible con fecha y hora.');
        }
        $date = CarbonImmutable::createFromFormat(self::ACCEPTED_FORMAT, $value, config('app.timezone'));
        if ($date && $date instanceof CarbonImmutable) {
            return $date;
        }
        throw new InvalidArgumentException('Formato de fecha no reconocido. Usa el formato DD/MM/YYYY HH:mm:ss.');
    }
}
