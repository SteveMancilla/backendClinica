<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreReportTemplateRequest;
use App\Http\Requests\UpdateReportTemplateRequest;
use App\Models\ReportTemplate;
use App\Models\Study;
use App\Models\User;
use App\Services\ReportTemplateCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ReportTemplateController extends Controller
{
    public function __construct(
        private readonly ReportTemplateCatalogService $catalogService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $catalog = $request->boolean('catalog');

        $query = ReportTemplate::query()
            ->with(['study.specialty', 'sections'])
            ->orderBy('name');

        if (! $catalog) {
            $query->where('status', 'active');
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function show(ReportTemplate $reportTemplate): JsonResponse
    {
        $reportTemplate->load(['study.specialty', 'sections']);

        return response()->json(['data' => $reportTemplate]);
    }

    public function store(StoreReportTemplateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $template = DB::transaction(function () use ($data) {
            $template = ReportTemplate::create([
                'study_id' => $data['study_id'],
                'code' => $this->catalogService->generateUniqueCode($data['name']),
                'name' => $data['name'],
                'format_type' => $data['format_type'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'inactive',
                'version' => 1,
            ]);

            if (! empty($data['sections'])) {
                $this->catalogService->syncSections($template, $data['sections']);
            }

            if (($data['status'] ?? 'inactive') === 'active') {
                $this->catalogService->setAsActiveTemplate($template->fresh());
            }

            Study::where('id', $template->study_id)->update([
                'format_type' => $data['format_type'],
            ]);

            return $template->fresh(['study.specialty', 'sections']);
        });

        return response()->json([
            'message' => 'Plantilla registrada correctamente.',
            'data' => $template,
        ], 201);
    }

    public function update(UpdateReportTemplateRequest $request, ReportTemplate $reportTemplate): JsonResponse
    {
        $data = $request->validated();

        $template = DB::transaction(function () use ($data, $reportTemplate) {
            $reportTemplate->update(collect($data)->only([
                'study_id',
                'name',
                'format_type',
                'description',
                'status',
            ])->filter(fn ($value) => $value !== null)->all());

            if (array_key_exists('sections', $data)) {
                $this->catalogService->syncSections($reportTemplate, $data['sections'] ?? []);
            }

            $shouldActivate = ($data['activate'] ?? false)
                || (($data['status'] ?? null) === 'active');

            if ($shouldActivate) {
                $this->catalogService->setAsActiveTemplate($reportTemplate->fresh());
            }

            if (isset($data['format_type'])) {
                Study::where('id', $reportTemplate->study_id)->update([
                    'format_type' => $data['format_type'],
                ]);
            }

            return $reportTemplate->fresh(['study.specialty', 'sections']);
        });

        return response()->json([
            'message' => 'Plantilla actualizada correctamente.',
            'data' => $template,
        ]);
    }

    public function destroy(Request $request, ReportTemplate $reportTemplate): JsonResponse
    {
        $this->ensureAdmin($request);

        if (! $this->catalogService->canDelete($reportTemplate)) {
            throw new UnprocessableEntityHttpException(
                'No se puede eliminar: la plantilla está en uso por atenciones o informes. Puede desactivarla.',
            );
        }

        $reportTemplate->delete();

        return response()->json([
            'message' => 'Plantilla eliminada correctamente.',
        ]);
    }

    public function restoreDefaults(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $seeder = new \Database\Seeders\ReportTemplateSeeder;
        $seeder->run();

        return response()->json([
            'message' => 'Plantillas predeterminadas restauradas (ecografía abdomen superior y radiografía de tórax).',
        ]);
    }

    private function ensureAdmin(Request $request): void
    {
        $user = $request->attributes->get('acting_user');
        if (! $user instanceof User || ! $user->isAdmin()) {
            throw new AccessDeniedHttpException('Solo el administrador puede gestionar plantillas.');
        }
    }
}
