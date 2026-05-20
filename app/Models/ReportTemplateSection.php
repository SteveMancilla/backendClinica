<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportTemplateSection extends Model
{
    protected $fillable = [
        'report_template_id',
        'title',
        'order_index',
        'base_text',
        'is_required',
        'voice_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'voice_enabled' => 'boolean',
        ];
    }

    public function reportTemplate(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function medicalReportSections(): HasMany
    {
        return $this->hasMany(MedicalReportSection::class);
    }
}
