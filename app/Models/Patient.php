<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = [
        'dni',
        'full_name',
        'age',
        'sex',
        'phone',
        'address',
        'email',
        'origin',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
        'status',
        'registered_by',
        'primary_doctor_id',
    ];

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function primaryDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_doctor_id');
    }

    public function medicalAttentions(): HasMany
    {
        return $this->hasMany(MedicalAttention::class);
    }

    public function medicalReports(): HasMany
    {
        return $this->hasMany(MedicalReport::class);
    }
}
