<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Study extends Model
{
    protected $fillable = [
        'specialty_id',
        'code',
        'name',
        'block',
        'format_type',
        'status',
    ];

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function reportTemplates(): HasMany
    {
        return $this->hasMany(ReportTemplate::class);
    }

    public function activeReportTemplate(): ?ReportTemplate
    {
        return $this->reportTemplates()
            ->where('status', 'active')
            ->orderByDesc('version')
            ->first();
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
