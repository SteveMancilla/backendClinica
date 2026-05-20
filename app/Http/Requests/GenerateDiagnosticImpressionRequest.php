<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateDiagnosticImpressionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sections' => ['sometimes', 'array'],
            'sections.*.id' => ['required_with:sections', 'integer', 'exists:medical_report_sections,id'],
            'sections.*.content' => ['nullable', 'string'],
        ];
    }
}
