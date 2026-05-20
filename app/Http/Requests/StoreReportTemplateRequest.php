<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->attributes->get('acting_user');

        return $user instanceof User && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'study_id' => ['required', 'integer', 'exists:studies,id'],
            'name' => ['required', 'string', 'max:255'],
            'format_type' => ['required', 'string', Rule::in(['structured', 'narrative'])],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'sections' => ['nullable', 'array'],
            'sections.*.title' => ['required_with:sections', 'string', 'max:255'],
            'sections.*.order_index' => ['nullable', 'integer', 'min:1'],
            'sections.*.base_text' => ['nullable', 'string'],
            'sections.*.is_required' => ['nullable', 'boolean'],
            'sections.*.voice_enabled' => ['nullable', 'boolean'],
        ];
    }
}
