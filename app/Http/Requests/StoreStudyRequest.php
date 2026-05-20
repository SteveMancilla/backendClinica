<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->attributes->get('acting_user');

        return $user instanceof User && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'specialty_id' => ['required', 'integer', 'exists:specialties,id'],
            'code' => ['nullable', 'string', 'max:80', 'unique:studies,code'],
            'name' => ['required', 'string', 'max:255'],
            'block' => ['required', 'string', Rule::in([
                'Ecografía general',
                'Ecografía de partes blandas',
                'Ecografía articular',
                'Ecografía Doppler',
                'Elastografías',
                'Procedimientos',
                'Biopsias',
                'Radiografías domiciliarias',
            ])],
            'format_type' => ['required', 'string', Rule::in(['structured', 'narrative'])],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
