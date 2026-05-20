<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PatientVisibilityService
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isDoctor()) {
            $assistantIds = $user->assistants()->pluck('id');

            return $query->where(function (Builder $q) use ($user, $assistantIds) {
                $q->where('primary_doctor_id', $user->id)
                    ->orWhere('registered_by', $user->id)
                    ->orWhereIn('registered_by', $assistantIds)
                    ->orWhereHas('medicalAttentions', fn (Builder $a) => $a->where('doctor_id', $user->id));
            });
        }

        if ($user->isAssistant()) {
            return $query->where(function (Builder $q) use ($user) {
                $q->where('registered_by', $user->id)
                    ->orWhereHas('medicalAttentions', function (Builder $a) use ($user) {
                        $a->where('assistant_id', $user->id)
                            ->orWhere('created_by', $user->id);
                    });
            });
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function applyRegistrationMeta(array $data, User $actor): array
    {
        $data['registered_by'] = $actor->id;

        if ($actor->isAssistant() && $actor->associated_doctor_id) {
            $data['primary_doctor_id'] = $actor->associated_doctor_id;
        } elseif ($actor->isDoctor()) {
            $data['primary_doctor_id'] = $actor->id;
        } else {
            $data['primary_doctor_id'] = null;
        }

        return $data;
    }
}
