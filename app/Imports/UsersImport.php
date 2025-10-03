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

        $emailVerifiedAt = EmailVerifiedAtFormat::normalize($data['email_verified_at'] ?? null);
        $password = $this->preparePassword($data['password'] ?? null);

        $payload = array_filter([
            'name' => $data['name'],
            'email' => $email,
            'role' => $data['role'] ?? 'user',
            'country_id' => $data['country_id'],
            'email_verified_at' => $emailVerifiedAt,
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
    private function sanitizeRow(array $row): array
    {
        $name = Arr::get($row, 'name');
        $email = Arr::get($row, 'email');
        $role = Arr::get($row, 'role');
        $countryId = Arr::get($row, 'country_id');

        return [
            'name' => is_string($name) ? trim($name) : $name,
            'email' => is_string($email) ? strtolower(trim($email)) : $email,
            'password' => Arr::get($row, 'password'),
            'role' => $this->normalizeRole($role),
            'country_id' => $this->normalizeCountryId($countryId),
            'email_verified_at' => Arr::get($row, 'email_verified_at'),
        ];
    }

    private function normalizeRole(mixed $role): ?string
    {
        if (!is_string($role)) {
            return null;
        }

        $normalized = strtolower(trim($role));

        return in_array($normalized, ['admin', 'user'], true) ? $normalized : null;
    }

    private function normalizeCountryId(mixed $countryId): ?int
    {
        if ($countryId === null) {
            return null;
        }

        if ($countryId === '') {
            return null;
        }

        return (int) $countryId;
    }

    private function preparePassword(mixed $password): ?string
    {
        if (!is_string($password)) {
            return null;
        }

        $password = trim($password);

        if ($password === '') {
            return null;
        }

        $info = password_get_info($password);

        if (($info['algo'] ?? 0) === 0) {
            return Hash::make($password);
        }

        if (Hash::needsRehash($password)) {
            return Hash::make($password);
        }

        return $password;
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
