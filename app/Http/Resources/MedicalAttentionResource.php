<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalAttentionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'assistant_id' => $this->assistant_id,
            'specialty_id' => $this->specialty_id,
            'study_id' => $this->study_id,
            'report_template_id' => $this->report_template_id,
            'attention_date' => $this->attention_date?->format('Y-m-d'),
            'attention_time' => is_string($this->attention_time)
                ? substr($this->attention_time, 0, 5)
                : $this->attention_time,
            'origin' => $this->origin,
            'reason' => $this->reason,
            'observations' => $this->observations,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'patient' => $this->whenLoaded('patient'),
            'doctor' => $this->whenLoaded('doctor', fn () => $this->doctor?->only([
                'id', 'full_name', 'email', 'specialty', 'role',
            ])),
            'assistant' => $this->whenLoaded('assistant', fn () => $this->assistant?->only([
                'id', 'full_name', 'email', 'position', 'role',
            ])),
            'specialty' => $this->whenLoaded('specialty'),
            'study' => $this->whenLoaded('study'),
            'report_template' => $this->whenLoaded('reportTemplate'),
            'medical_report' => MedicalReportResource::make($this->whenLoaded('medicalReport')),
        ];
    }
}
