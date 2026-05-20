<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->attributes->get('acting_user');

        return $user instanceof User && $user->isAdmin();
    }

    public function rules(): array
    {
        $studyId = $this->route('study')?->id;

        return [
            'specialty_id' => ['sometimes', 'integer', 'exists:specialties,id'],
            'code' => ['nullable', 'string', 'max:80', Rule::unique('studies', 'code')->ignore($studyId)],
            'name' => ['sometimes', 'string', 'max:255'],
            'block' => ['sometimes', 'string', Rule::in([
                'Ecografía general',
                'Ecografía de partes blandas',
                'Ecografía articular',
                'Ecografía Doppler',
                'Elastografías',
                'Procedimientos',
                'Biopsias',
                'Radiografías domiciliarias',
            ])],
            'format_type' => ['sometimes', 'string', Rule::in(['structured', 'narrative'])],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
