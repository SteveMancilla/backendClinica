<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class MedicalReportVisibilityService
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isDoctor()) {
            return $query->where('doctor_id', $user->id);
        }

        if ($user->isAssistant() && $user->associated_doctor_id) {
            return $query->where('doctor_id', $user->associated_doctor_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
