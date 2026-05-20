<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->get('acting_user') instanceof User;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(6)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var User $user */
            $user = $this->attributes->get('acting_user');

            if (! Hash::check($this->input('current_password'), $user->password)) {
                $validator->errors()->add(
                    'current_password',
                    'La contraseña actual no es correcta.',
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
        ];
    }
}
