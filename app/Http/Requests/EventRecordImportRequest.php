<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRecordImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'records_file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ];
    }

    public function attributes(): array
    {
        return [
            'records_file' => 'archivo de registros',
        ];
    }

    public function messages(): array
    {
        return [
            'records_file.required' => 'Debes proporcionar un archivo.',
            'records_file.mimes' => 'Solo se permiten archivos Excel o CSV.',
            'records_file.max' => 'El archivo no puede superar los 5MB.',
        ];
    }
}
