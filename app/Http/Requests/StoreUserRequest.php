<?php

namespace App\Http\Requests;

use App\Http\Traits\HandleValidationErrors;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    use HandleValidationErrors;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Si se está especificando un rol admin, solo admins pueden crear otros admins
        if ($this->has('role') && $this->input('role') === 'admin') {
            return $this->user() && $this->user()->isAdmin();
        }

        return true; // Registro público permitido para usuarios normales
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', // Solo letras y espacios
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
                'not_regex:/[<>"\'()]/', // Prevenir XSS
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/', // Al menos 1 minúscula, 1 mayúscula, 1 número, 1 símbolo
            ],
            'role' => [
                'sometimes',
                'string',
                'in:admin,user',
            ],
        ];
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
            'email.email' => 'El email debe tener un formato válido y un dominio real.',
            'email.unique' => 'Este email ya está registrado.',
            'email.not_regex' => 'El email contiene caracteres no permitidos.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede tener más de 255 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password.regex' => 'La contraseña debe contener al menos: 1 letra minúscula, 1 mayúscula, 1 número y 1 símbolo especial.',
            'role.in' => 'El rol debe ser admin o user.',
        ];
    }
}
