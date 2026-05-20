<?php

namespace App\Http\Resources;

use App\Support\MedicalReportPdfFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medical_attention_id' => $this->medical_attention_id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'study_id' => $this->study_id,
            'report_template_id' => $this->report_template_id,
            'status' => $this->status,
            'diagnostic_impression' => $this->diagnostic_impression,
            'pdf_path' => $this->pdf_path,
            'concluded_at' => $this->concluded_at,
            'generated_pdf_at' => $this->generated_pdf_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'patient' => $this->whenLoaded('patient'),
            'doctor' => $this->whenLoaded('doctor', fn () => $this->doctor?->only([
                'id', 'full_name', 'email', 'role', 'specialty', 'position',
            ])),
            'reporting_physician' => $this->when(
                $this->relationLoaded('doctor'),
                fn () => MedicalReportPdfFormatter::formatReportingPhysicianPayload(
                    MedicalReportPdfFormatter::resolveReportingPhysician($this->resource),
                ),
            ),
            'study' => $this->whenLoaded('study'),
            'report_template' => $this->whenLoaded('reportTemplate'),
            'sections' => $this->whenLoaded('sections'),
        ];
    }
}
