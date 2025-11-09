<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'regex:/^\d{6}$/', 'required_without:recovery_code'],
            'recovery_code' => ['nullable', 'string', 'min:6', 'max:255', 'required_without:code'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'El código debe tener 6 dígitos.',
            'code.required_without' => 'Ingresa el código de tu app o uno de recuperación.',
            'recovery_code.required_without' => 'Debes ingresar un código de recuperación si no tienes acceso a la app.',
        ];
    }
}
