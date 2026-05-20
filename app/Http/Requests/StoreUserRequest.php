<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->attributes->get('acting_user');

        return $actor instanceof User && ($actor->isAdmin() || $actor->isDoctor());
    }

    public function rules(): array
    {
        return [
            'dni' => ['nullable', 'string', 'max:20', 'unique:users,dni'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in(['admin', 'doctor', 'assistant'])],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive'])],
            'specialty' => ['nullable', 'string', 'max:255'],
            'cmp' => ['nullable', 'string', 'max:50'],
            'rne' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'origin_city' => ['nullable', 'string', 'max:120'],
            'support_area' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'associated_doctor_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var User|null $actor */
            $actor = $this->attributes->get('acting_user');
            $role = $this->input('role');

            if ($role === 'assistant' && ! $this->input('associated_doctor_id')) {
                $validator->errors()->add(
                    'associated_doctor_id',
                    'El asistente debe tener un médico asociado.',
                );
            }

            if ($actor?->isDoctor()) {
                if ($role !== 'assistant') {
                    $validator->errors()->add('role', 'Como médico solo puedes crear asistentes.');
                }

                if ((int) $this->input('associated_doctor_id') !== $actor->id) {
                    $validator->errors()->add(
                        'associated_doctor_id',
                        'El asistente debe estar asociado a tu usuario médico.',
                    );
                }
            }
        });
    }
}
