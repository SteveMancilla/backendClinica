<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicalReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'diagnostic_impression' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in([
                'missing_report',
                'missing_diagnostic_impression',
                'in_review',
                'concluded',
                'pdf_generated',
            ])],
            'sections' => ['nullable', 'array'],
            'sections.*.id' => ['required_with:sections', 'integer', 'exists:medical_report_sections,id'],
            'sections.*.content' => ['nullable', 'string'],
        ];
    }
}
