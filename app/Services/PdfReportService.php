<?php

namespace App\Services;

use App\Models\MedicalReport;
use App\Support\MedicalReportPdfFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfReportService
{
    /**
     * Genera el PDF, actualiza el informe y devuelve la ruta relativa en el disco configurado.
     */
    public function generate(MedicalReport $report, bool $forceRegenerate = false): string
    {
        $report->load([
            'sections',
            'patient.primaryDoctor',
            'doctor',
            'study',
            'reportTemplate',
            'medicalAttention.assistant.associatedDoctor',
        ]);

        $this->assertCanGenerate($report);

        $disk = (string) config('clinic.pdf.disk', 'local');
        $relativePath = $this->buildStoragePath($report);

        if (
            ! $forceRegenerate
            && filled($report->pdf_path)
            && Storage::disk($disk)->exists($report->pdf_path)
        ) {
            return $report->pdf_path;
        }

        $pdf = Pdf::loadView('reports.medical-report', $this->buildViewData($report))
            ->setPaper('a4', 'portrait');

        Storage::disk($disk)->put($relativePath, $pdf->output());

        $report->update([
            'pdf_path' => $relativePath,
            'generated_pdf_at' => now(),
            'status' => 'pdf_generated',
        ]);

        $report->medicalAttention?->update([
            'status' => 'pdf_generated',
        ]);

        return $relativePath;
    }

    public function buildDownloadFilename(MedicalReport $report): string
    {
        $report->loadMissing(['patient', 'study', 'medicalAttention']);

        $patientSlug = Str::slug($report->patient?->full_name ?? 'paciente', '_');
        $studySlug = Str::slug($report->study?->name ?? 'estudio', '_');
        $date = $report->medicalAttention?->attention_date?->format('Y-m-d')
            ?? $report->created_at?->format('Y-m-d')
            ?? now()->format('Y-m-d');

        return "informe_{$patientSlug}_{$studySlug}_{$date}.pdf";
    }

    public function invalidateStoredPdf(MedicalReport $report): void
    {
        if (! filled($report->pdf_path)) {
            return;
        }

        $disk = (string) config('clinic.pdf.disk', 'local');

        if (Storage::disk($disk)->exists($report->pdf_path)) {
            Storage::disk($disk)->delete($report->pdf_path);
        }

        $report->update([
            'pdf_path' => null,
            'generated_pdf_at' => null,
        ]);
    }

    public function resolveAbsolutePath(MedicalReport $report): ?string
    {
        if (! filled($report->pdf_path)) {
            return null;
        }

        $disk = (string) config('clinic.pdf.disk', 'local');

        if (! Storage::disk($disk)->exists($report->pdf_path)) {
            return null;
        }

        return Storage::disk($disk)->path($report->pdf_path);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildViewData(MedicalReport $report): array
    {
        $patient = $report->patient;
        $formatType = $report->reportTemplate?->format_type
            ?? $report->study?->format_type
            ?? 'structured';

        $physician = MedicalReportPdfFormatter::formatPhysicianBlock(
            MedicalReportPdfFormatter::resolveReportingPhysician($report),
        );

        return [
            'clinicName' => config('clinic.name'),
            'centerName' => config('clinic.center_name'),
            'tagline' => config('clinic.tagline'),
            'clinicAddress' => config('clinic.address'),
            'clinicPhone' => config('clinic.phone'),
            'patientName' => MedicalReportPdfFormatter::uppercase($patient?->full_name ?? '—'),
            'patientAge' => $patient?->age ?? '—',
            'studyName' => MedicalReportPdfFormatter::uppercase($report->study?->name ?? '—'),
            'origin' => MedicalReportPdfFormatter::uppercase(
                $report->medicalAttention?->origin ?? $patient?->origin ?? '—',
            ),
            'attentionDateTime' => MedicalReportPdfFormatter::formatAttentionDateTime($report),
            'formatType' => $formatType,
            'sections' => MedicalReportPdfFormatter::formatSections($report, $formatType),
            'impressionItems' => MedicalReportPdfFormatter::formatDiagnosticImpression(
                (string) $report->diagnostic_impression,
            ),
            'physicianName' => $physician['name'],
            'physicianSpecialty' => $physician['specialty'],
        ];
    }

    private function buildStoragePath(MedicalReport $report): string
    {
        $base = trim((string) config('clinic.pdf.directory', 'medical-reports'), '/');
        $year = now()->format('Y');
        $month = now()->format('m');

        return "{$base}/{$year}/{$month}/informe_{$report->id}.pdf";
    }

    private function assertCanGenerate(MedicalReport $report): void
    {
        if (! filled(trim((string) $report->diagnostic_impression))) {
            throw new \InvalidArgumentException(
                'Debe registrar la impresión diagnóstica antes de generar el PDF.',
            );
        }

        $hasSectionContent = $report->sections->contains(
            fn ($section) => filled(trim((string) ($section->content ?? ''))),
        );

        if (! $hasSectionContent) {
            throw new \InvalidArgumentException(
                'El informe debe tener hallazgos dictados en al menos una sección.',
            );
        }

        if (! in_array($report->status, ['concluded', 'pdf_generated', 'in_review'], true)) {
            throw new \InvalidArgumentException(
                'El informe debe estar en revisión o concluido para generar el PDF.',
            );
        }
    }
}
