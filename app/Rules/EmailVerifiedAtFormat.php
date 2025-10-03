<?php

namespace App\Rules;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EmailVerifiedAtFormat implements ValidationRule
{
    public const HUMAN_READABLE_EXPECTATION = 'AAAA-MM-DD HH:mm:ss (24 horas)';

    /**
     * Lista de formatos soportados para interpretar valores manuales.
     *
     * @var array<int, string>
     */
    private const ACCEPTED_FORMATS = [
        'Y-m-d H:i:s',
        'Y-m-d\TH:i:s',
        'Y-m-d\TH:i:sP',
        'Y-m-d H:i',
        'Y/m/d H:i:s',
    ];

    /**
     * Validate a given value.
     *
     * @param  \Closure(string): void  $fail
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        try {
            self::normalize($value);
        } catch (InvalidArgumentException $exception) {
            $fail(
                __('El campo :attribute debe tener un formato de fecha y hora válido. Usa el formato :format o configura la celda como fecha/hora.', [
                    'attribute' => $attribute,
                    'format' => self::HUMAN_READABLE_EXPECTATION,
                ])
            );
        }
    }

    /**
     * Convert the provided value into a Carbon instance or null when empty.
     *
     * @throws InvalidArgumentException
     */
    public static function normalize(mixed $value): ?CarbonImmutable
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                $dateTime = ExcelDate::excelToDateTimeObject((float) $value);

                return CarbonImmutable::instance($dateTime)
                    ->setTimezone(config('app.timezone'));
            } catch (\Throwable $exception) {
                throw new InvalidArgumentException('El valor numérico no puede convertirse en fecha/hora válida.', 0, $exception);
            }
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('El valor debe ser una cadena compatible con fecha y hora.');
        }

        foreach (self::ACCEPTED_FORMATS as $format) {
            $date = CarbonImmutable::createFromFormat($format, $value, config('app.timezone'));

            if ($date instanceof CarbonImmutable) {
                return $date;
            }
        }

        try {
            return CarbonImmutable::parse($value, config('app.timezone'));
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException('Formato de fecha no reconocido.', 0, $exception);
        }
    }
}
