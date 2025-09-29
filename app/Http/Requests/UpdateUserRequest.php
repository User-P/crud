<?php

namespace App\Http\Requests;

use App\Http\Traits\HandleValidationErrors;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    use HandleValidationErrors;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $targetUser = $this->route('user');
        $currentUser = $this->user();

        // Si no hay usuario autenticado, denegar
        if (!$currentUser) {
            return false;
        }

        if (!$targetUser) {
            return false;
        }

        // Si se está intentando cambiar el rol, solo admins pueden hacerlo
        if ($this->has('role') && $this->input('role') !== $targetUser->role) {
            return $currentUser->isAdmin();
        }

        // Los usuarios pueden actualizar su propio perfil, los admins pueden actualizar cualquiera
        return $currentUser->id === $targetUser->id || $currentUser->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', // Solo letras y espacios
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email:rfc',
                'max:255',
                'unique:users,email,' . $userId,
                'not_regex:/[<>"\'()]/', // Prevenir XSS
            ],
            'password' => [
                'sometimes',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/', // Requisitos de seguridad
            ],
            'current_password' => Rule::when(
                $this->shouldValidateCurrentPassword(),
                ['required_with:password', 'string', 'current_password'],
                ['nullable', 'string']
            ),
            'role' => [
                'sometimes',
                'string',
                'in:admin,user',
            ],
            'country_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:countries,id',
            ],
        ];
    }

    private function shouldValidateCurrentPassword(): bool
    {
        $targetUser = $this->route('user');
        $currentUser = $this->user();

        return $targetUser !== null
            && $currentUser !== null
            && $currentUser->is($targetUser);
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está registrado.',
            'email.not_regex' => 'El email contiene caracteres no permitidos.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede tener más de 255 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password.regex' => 'La contraseña debe contener al menos: 1 letra minúscula, 1 mayúscula, 1 número y 1 símbolo especial.',
            'current_password.required_with' => 'La contraseña actual es requerida para cambiar la contraseña.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'role.in' => 'El rol debe ser admin o user.',
        ];
    }
}
