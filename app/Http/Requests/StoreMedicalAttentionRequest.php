<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicalAttentionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['required', 'integer', 'exists:users,id'],
            'assistant_id' => ['nullable', 'integer', 'exists:users,id'],
            'study_id' => ['required', 'integer', 'exists:studies,id'],
            'attention_date' => ['required', 'date'],
            'attention_time' => ['required', 'date_format:H:i,H:i:s'],
            'origin' => ['required', 'string', Rule::in([
                'Particular',
                'Emergencia',
                'Consulta externa',
                'Referido',
                'Convenio',
                'Hospitalización',
            ])],
            'reason' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in([
                'pending_study',
                'study_done',
                'missing_report',
                'missing_diagnostic_impression',
                'in_review',
                'concluded',
                'pdf_generated',
            ])],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
