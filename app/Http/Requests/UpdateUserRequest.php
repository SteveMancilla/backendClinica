<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $actor */
        $actor = $this->attributes->get('acting_user');
        /** @var User|null $target */
        $target = $this->route('user');

        if (! $actor || ! $target) {
            return false;
        }

        if ($actor->isAdmin()) {
            return true;
        }

        if ($actor->isDoctor()) {
            return $target->role === 'assistant'
                && (int) $target->associated_doctor_id === $actor->id;
        }

        return $actor->id === $target->id;
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'dni' => ['sometimes', 'string', 'max:20', Rule::unique('users', 'dni')->ignore($user->id)],
            'full_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
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
}
