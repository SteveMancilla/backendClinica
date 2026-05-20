<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalReportSection extends Model
{
    protected $fillable = [
        'medical_report_id',
        'report_template_section_id',
        'title',
        'order_index',
        'base_text',
        'content',
    ];

    public function medicalReport(): BelongsTo
    {
        return $this->belongsTo(MedicalReport::class);
    }

    public function reportTemplateSection(): BelongsTo
    {
        return $this->belongsTo(ReportTemplateSection::class);
    }
}
