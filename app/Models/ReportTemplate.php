<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportTemplate extends Model
{
    protected $fillable = [
        'study_id',
        'code',
        'name',
        'format_type',
        'description',
        'status',
        'version',
    ];

    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ReportTemplateSection::class)->orderBy('order_index');
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
