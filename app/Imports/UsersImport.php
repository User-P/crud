<?php

namespace App\Imports;

use App\Models\User;
use App\Rules\EmailVerifiedAtFormat;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;

class UsersImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable;
    use SkipsFailures;

    private int $created = 0;

    private int $updated = 0;

    private int $unchanged = 0;

    /**
     * Procesar cada fila del archivo importado.
     */
    public function onRow(Row $row): void
    {
        $data = $this->sanitizeRow($row->toArray());
        $email = $data['email'];

        // Normalizar y validar el campo email_verified_at (formato estricto)
        try {
            $emailVerifiedAt = EmailVerifiedAtFormat::normalize($data['email_verified_at'] ?? null);
        } catch (\InvalidArgumentException $e) {
            $this->onFailure(new Failure(
                $row->getIndex(),
                'email_verified_at',
                [$e->getMessage()],
                $row->toArray()
            ));
            return;
        }
        $password = $this->preparePassword($data['password'] ?? null);

        $payload = array_filter([
            'name' => $data['name'],
            'email' => $email,
            'role' => $data['role'] ?? 'user',
            'country_id' => $data['country_id'],
            // Guardar como string compatible con timestamp si existe
            'email_verified_at' => $emailVerifiedAt?->format('Y-m-d H:i:s'),
        ], static fn($value) => $value !== null);

        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser) {
            $updated = $this->updateExistingUser($existingUser, $payload, $password);

            if ($updated) {
                $this->updated++;
            } else {
                $this->unchanged++;
            }

            return;
        }

        if ($password === null) {
            $this->onFailure(new Failure(
                $row->getIndex(),
                "password",
                ['La contraseña es obligatoria para crear un nuevo usuario.'],
                $row->toArray()
            ));

            return;
        }

        $this->createUser($payload, $password);
        $this->created++;
    }

    /**
     * Validation rules per spreadsheet row.
     *
     * @return array<string, array<int, string|EmailVerifiedAtFormat>>
     */
    public function rules(): array
    {
        $nameRegex = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u';
        return [
            '*.name' => ['required', 'string', 'min:2', 'max:255', "regex:{$nameRegex}"],
            '*.email' => ['required', 'string', 'email:rfc', 'max:255'],
            '*.password' => ['nullable', 'string', 'min:8', 'max:255'],
            '*.role' => ['nullable', 'string', 'in:admin,user'],
            '*.country_id' => ['nullable', 'integer', 'exists:countries,id'],
            // Validación estricta del formato de fecha
            '*.email_verified_at' => ['nullable', new EmailVerifiedAtFormat()],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function customValidationMessages(): array
    {
        return [
            '*.name.required' => 'El nombre es obligatorio.',
            '*.name.regex' => 'El nombre solo puede contener letras y espacios.',
            '*.email.required' => 'El email es obligatorio.',
            '*.email.email' => 'El email debe tener un formato válido.',
            '*.password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            '*.password.max' => 'La contraseña no puede tener más de 255 caracteres.',
            '*.role.in' => 'El rol debe ser admin o user.',
            '*.country_id.exists' => 'El country_id indicado no existe.',
            '*.email_verified_at' => 'El campo email_verified_at debe tener el formato DD/MM/YYYY HH:mm:ss o estar vacío.',
        ];
    }

    /**
     * Resumen numérico de la importación.
     *
     * @return array<string, int>
     */
    public function getSummary(): array
    {
        $skipped = $this->failures()->count();

        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'unchanged' => $this->unchanged,
            'skipped' => $skipped,
            'processed' => $this->created + $this->updated + $this->unchanged + $skipped,
        ];
    }

    /**
     * Failures formatted for UI consumption.
     *
     * @return array<int, array<string, mixed>>
     */
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

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    // Simplificar sanitizeRow, solo limpiar strings y dejar validación a las reglas.
    private function sanitizeRow(array $row): array
    {
        return [
            'name' => isset($row['name']) && is_string($row['name']) ? trim($row['name']) : $row['name'],
            'email' => isset($row['email']) && is_string($row['email']) ? strtolower(trim($row['email'])) : $row['email'],
            'password' => $row['password'] ?? null,
            'role' => $row['role'] ?? null,
            'country_id' => $row['country_id'] ?? null,
            'email_verified_at' => $row['email_verified_at'] ?? null,
        ];
    }


    // Método normalizeRole eliminado por redundante.


    // Método normalizeCountryId eliminado por redundante.

    // Simplificar preparePassword: solo hashear si no está hasheado.
    private function preparePassword(mixed $password): ?string
    {
        if (!is_string($password) || trim($password) === '') {
            return null;
        }
        $password = trim($password);
        // Si ya está hasheado (bcrypt), no volver a hashear
        if (preg_match('/^\$2y\$/', $password)) {
            return $password;
        }
        return Hash::make($password);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function createUser(array $payload, string $password): void
    {
        User::create(array_merge($payload, [
            'password' => $password,
            'role' => $payload['role'] ?? 'user',
        ]));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function updateExistingUser(User $user, array $payload, ?string $password): bool
    {
        $updates = $payload;

        if ($password !== null) {
            $updates['password'] = $password;
        }

        $updates = array_filter(
            $updates,
            static fn($value) => $value !== null
        );

        if (empty($updates)) {
            return false;
        }

        $user->fill($updates);

        if (!$user->isDirty()) {
            return false;
        }

        $user->save();

        return true;
    }
}
