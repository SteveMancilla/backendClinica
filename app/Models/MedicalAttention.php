<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MedicalAttention extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'assistant_id',
        'specialty_id',
        'study_id',
        'report_template_id',
        'attention_date',
        'attention_time',
        'origin',
        'reason',
        'observations',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'attention_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }

    public function reportTemplate(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function medicalReport(): HasOne
    {
        return $this->hasOne(MedicalReport::class);
    }
}
