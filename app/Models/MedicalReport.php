<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalReport extends Model
{
    protected $fillable = [
        'medical_attention_id',
        'patient_id',
        'doctor_id',
        'study_id',
        'report_template_id',
        'status',
        'diagnostic_impression',
        'pdf_path',
        'concluded_at',
        'generated_pdf_at',
    ];

    protected function casts(): array
    {
        return [
            'concluded_at' => 'datetime',
            'generated_pdf_at' => 'datetime',
        ];
    }

    public function medicalAttention(): BelongsTo
    {
        return $this->belongsTo(MedicalAttention::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }

    public function reportTemplate(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(MedicalReportSection::class)->orderBy('order_index');
    }
}
