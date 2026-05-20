<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'dni',
        'full_name',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'status',
        'specialty',
        'cmp',
        'rne',
        'associated_doctor_id',
        'position',
        'origin_city',
        'support_area',
        'notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function associatedDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'associated_doctor_id');
    }

    public function assistants(): HasMany
    {
        return $this->hasMany(User::class, 'associated_doctor_id');
    }

    public function medicalAttentionsAsDoctor(): HasMany
    {
        return $this->hasMany(MedicalAttention::class, 'doctor_id');
    }

    public function medicalAttentionsAsAssistant(): HasMany
    {
        return $this->hasMany(MedicalAttention::class, 'assistant_id');
    }

    public function medicalReports(): HasMany
    {
        return $this->hasMany(MedicalReport::class, 'doctor_id');
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canSignMedicalReports(): bool
    {
        return in_array($this->role, ['doctor', 'admin'], true);
    }
}
