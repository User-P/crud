<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UserImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'users_file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ];
    }

    /**
     * Custom attribute names to show clearer validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'users_file' => 'archivo de usuarios',
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'users_file.required' => 'Debes seleccionar un archivo para importar.',
            'users_file.file' => 'El archivo seleccionado no es válido.',
            'users_file.mimes' => 'El archivo debe ser un Excel (.xlsx, .xls) o un CSV.',
            'users_file.max' => 'El archivo no puede superar los 5MB.',
        ];
    }
}
