<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->route('patient')?->id;

        return [
            'dni' => ['required', 'string', 'max:20', Rule::unique('patients', 'dni')->ignore($patientId)],
            'full_name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:0'],
            'sex' => ['required', 'string', Rule::in(['Masculino', 'Femenino'])],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'origin' => ['required', 'string', Rule::in([
                'Particular',
                'Emergencia',
                'Consulta externa',
                'Referido',
                'Convenio',
                'Hospitalización',
            ])],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
