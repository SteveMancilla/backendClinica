<?php

namespace App\Http\Controllers\Api;

use App\Support\MedicalReportIndexRelations;
use App\Http\Requests\GenerateDiagnosticImpressionRequest;
use App\Http\Requests\UpdateMedicalReportRequest;
use App\Http\Resources\MedicalReportResource;
use App\Models\MedicalReport;
use App\Models\MedicalReportSection;
use App\Models\User;
use App\Services\DiagnosticImpressionService;
use App\Services\MedicalReportVisibilityService;
use App\Services\PdfReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MedicalReportController extends Controller
{
    public function __construct(
        private readonly DiagnosticImpressionService $diagnosticImpressionService,
        private readonly MedicalReportVisibilityService $reportVisibility,
        private readonly PdfReportService $pdfReportService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $full = $request->boolean('full');

        $relations = $full
            ? MedicalReportIndexRelations::full()
            : MedicalReportIndexRelations::summary();

        $query = MedicalReport::with($relations)->latest();

        if ($patientId = $request->integer('patient_id')) {
            $query->where('patient_id', $patientId);
        }

        if ($status = $request->string('status')->toString()) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $actor = $request->attributes->get('acting_user');
        if ($actor instanceof User) {
            $this->reportVisibility->scopeVisibleTo($query, $actor);
        }

        return response()->json([
            'data' => MedicalReportResource::collection($query->get()),
        ]);
    }

    public function show(MedicalReport $medicalReport): JsonResponse
    {
        $medicalReport->load([
            'patient',
            'doctor:id,full_name,email,role,specialty,position',
            'study',
            'reportTemplate',
            'sections',
            'medicalAttention.doctor:id,full_name,email,role,specialty,position',
            'medicalAttention.assistant:id,full_name,associated_doctor_id,role',
            'medicalAttention.assistant.associatedDoctor:id,full_name,role,specialty,position',
        ]);

        return response()->json([
            'data' => new MedicalReportResource($medicalReport),
        ]);
    }

    public function update(UpdateMedicalReportRequest $request, MedicalReport $medicalReport): JsonResponse
    {
        $data = $request->validated();
        $medicalReport->load('sections');
        $hadStoredPdf = filled($medicalReport->pdf_path);
        $wasFinalized = in_array($medicalReport->status, ['concluded', 'pdf_generated'], true);
        $contentChanged = $this->reportContentChanged($medicalReport, $data);

        DB::transaction(function () use ($data, $medicalReport, $hadStoredPdf, $wasFinalized, $contentChanged) {
            if (isset($data['sections'])) {
                foreach ($data['sections'] as $sectionData) {
                    MedicalReportSection::where('id', $sectionData['id'])
                        ->where('medical_report_id', $medicalReport->id)
                        ->update(['content' => $sectionData['content'] ?? null]);
                }
            }

            $updates = array_filter([
                'diagnostic_impression' => $data['diagnostic_impression'] ?? null,
                'status' => $data['status'] ?? null,
            ], fn ($value) => $value !== null);

            if ($hadStoredPdf && $contentChanged) {
                $this->pdfReportService->invalidateStoredPdf($medicalReport);
                $medicalReport->refresh();

                if ($wasFinalized && ($updates['status'] ?? null) !== 'pdf_generated') {
                    $updates['status'] = $updates['status'] ?? 'in_review';
                }
            }

            if ($updates !== []) {
                $medicalReport->update($updates);
            }

            if (
                $wasFinalized
                && ($medicalReport->fresh()->status === 'in_review')
                && $medicalReport->medicalAttention
            ) {
                $medicalReport->medicalAttention->update(['status' => 'in_review']);
            }
        });

        $medicalReport->refresh()->load(['sections', 'patient', 'doctor', 'study', 'medicalAttention']);

        return response()->json([
            'message' => 'Informe actualizado correctamente.',
            'data' => new MedicalReportResource($medicalReport),
        ]);
    }

    public function generateDiagnosticImpression(
        GenerateDiagnosticImpressionRequest $request,
        MedicalReport $medicalReport,
    ): JsonResponse {
        if ($sections = $request->validated('sections')) {
            foreach ($sections as $sectionData) {
                MedicalReportSection::where('id', $sectionData['id'])
                    ->where('medical_report_id', $medicalReport->id)
                    ->update(['content' => $sectionData['content'] ?? null]);
            }
            $medicalReport->refresh();
        }

        try {
            $result = $this->diagnosticImpressionService->generate($medicalReport);
        } catch (\InvalidArgumentException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $sourceLabel = $result['source'] === 'ollama'
            ? 'Impresión generada con IA (Ollama).'
            : 'Impresión generada con motor clínico de respaldo (Ollama no disponible).';

        return response()->json([
            'message' => $sourceLabel,
            'data' => [
                'diagnostic_impression' => $result['impression'],
                'suggestions' => $result['suggestions'],
                'source' => $result['source'],
                'model' => $result['model'],
            ],
        ]);
    }

    public function conclude(MedicalReport $medicalReport): JsonResponse
    {
        $medicalReport->load('sections', 'medicalAttention');

        $hasContent = $medicalReport->sections->contains(
            fn ($section) => filled(trim((string) ($section->content ?? $section->base_text))),
        );

        if (! $hasContent) {
            throw new UnprocessableEntityHttpException(
                'El informe debe tener contenido en al menos una sección antes de concluir.',
            );
        }

        if (! filled(trim((string) $medicalReport->diagnostic_impression))) {
            throw new UnprocessableEntityHttpException(
                'Debe registrar la impresión diagnóstica antes de concluir el informe.',
            );
        }

        DB::transaction(function () use ($medicalReport) {
            $medicalReport->update([
                'status' => 'concluded',
                'concluded_at' => now(),
            ]);

            $medicalReport->medicalAttention?->update([
                'status' => 'concluded',
            ]);
        });

        $medicalReport->refresh()->load([
            'sections',
            'patient',
            'doctor:id,full_name,email,specialty',
            'study',
            'medicalAttention',
        ]);

        return response()->json([
            'message' => 'Informe concluido correctamente.',
            'data' => new MedicalReportResource($medicalReport),
        ]);
    }

    public function generatePdf(Request $request, MedicalReport $medicalReport): JsonResponse
    {
        $force = $request->boolean('regenerate');

        try {
            $path = $this->pdfReportService->generate($medicalReport, $force);
        } catch (\InvalidArgumentException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $medicalReport->refresh()->load([
            'sections',
            'patient',
            'doctor:id,full_name,email,specialty,cmp',
            'study',
            'medicalAttention',
        ]);

        return response()->json([
            'message' => 'PDF del informe generado correctamente.',
            'data' => [
                'report' => new MedicalReportResource($medicalReport),
                'pdf_path' => $path,
                'download_url' => url("/api/medical-reports/{$medicalReport->id}/pdf"),
            ],
        ]);
    }

    public function downloadPdf(Request $request, MedicalReport $medicalReport): StreamedResponse|JsonResponse
    {
        $regenerate = $request->boolean('regenerate');

        try {
            if ($regenerate || ! filled($medicalReport->pdf_path)) {
                $this->pdfReportService->generate($medicalReport, $regenerate);
                $medicalReport->refresh();
            }
        } catch (\InvalidArgumentException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $disk = (string) config('clinic.pdf.disk', 'local');
        $path = $medicalReport->pdf_path;

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            throw new NotFoundHttpException('No se encontró el archivo PDF del informe.');
        }

        $filename = $this->pdfReportService->buildDownloadFilename($medicalReport);

        return Storage::disk($disk)->download($path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function reportContentChanged(MedicalReport $medicalReport, array $data): bool
    {
        if (array_key_exists('diagnostic_impression', $data)) {
            $newImpression = trim((string) ($data['diagnostic_impression'] ?? ''));
            $oldImpression = trim((string) ($medicalReport->diagnostic_impression ?? ''));
            if ($newImpression !== $oldImpression) {
                return true;
            }
        }

        if (! isset($data['sections'])) {
            return false;
        }

        $existing = $medicalReport->sections->keyBy('id');

        foreach ($data['sections'] as $sectionData) {
            $section = $existing->get($sectionData['id'] ?? null);
            if (! $section) {
                continue;
            }

            $newContent = trim((string) ($sectionData['content'] ?? ''));
            $oldContent = trim((string) ($section->content ?? ''));

            if ($newContent !== $oldContent) {
                return true;
            }
        }

        return false;
    }
}
