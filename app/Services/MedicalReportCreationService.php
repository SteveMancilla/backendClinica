<?php

namespace App\Services;

use App\Models\MedicalAttention;
use App\Models\MedicalReport;
use App\Models\ReportTemplate;
use Illuminate\Support\Facades\DB;

class MedicalReportCreationService
{
    public function createFromAttention(MedicalAttention $attention, ReportTemplate $template): MedicalReport
    {
        return DB::transaction(function () use ($attention, $template) {
            $report = MedicalReport::create([
                'medical_attention_id' => $attention->id,
                'patient_id' => $attention->patient_id,
                'doctor_id' => $attention->doctor_id,
                'study_id' => $attention->study_id,
                'report_template_id' => $template->id,
                'status' => 'missing_report',
            ]);

            $template->load('sections');

            foreach ($template->sections as $section) {
                $report->sections()->create([
                    'report_template_section_id' => $section->id,
                    'title' => $section->title,
                    'order_index' => $section->order_index,
                    'base_text' => $section->base_text,
                    'content' => null,
                ]);
            }

            return $report->load('sections');
        });
    }
}
