<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreStudyRequest;
use App\Http\Requests\UpdateStudyRequest;
use App\Models\Study;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class StudyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $catalog = $request->boolean('catalog');

        $query = Study::query()
            ->with([
                'specialty',
                'reportTemplates' => function ($relation) use ($catalog) {
                    $relation->with('sections')->orderByDesc('version');
                    if (! $catalog) {
                        $relation->where('status', 'active');
                    }
                },
            ])
            ->orderBy('name');

        if (! $catalog) {
            $query->where('status', 'active');
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function show(Study $study): JsonResponse
    {
        $study->load(['specialty', 'reportTemplates.sections']);

        return response()->json(['data' => $study]);
    }

    public function store(StoreStudyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $code = $data['code'] ?? $this->generateStudyCode($data['name']);

        $study = Study::create([
            'specialty_id' => $data['specialty_id'],
            'code' => $code,
            'name' => $data['name'],
            'block' => $data['block'],
            'format_type' => $data['format_type'],
            'status' => $data['status'] ?? 'active',
        ]);

        $study->load(['specialty', 'reportTemplates.sections']);

        return response()->json([
            'message' => 'Estudio registrado correctamente.',
            'data' => $study,
        ], 201);
    }

    public function update(UpdateStudyRequest $request, Study $study): JsonResponse
    {
        $study->update($request->validated());
        $study->load(['specialty', 'reportTemplates.sections']);

        return response()->json([
            'message' => 'Estudio actualizado correctamente.',
            'data' => $study,
        ]);
    }

    public function destroy(Request $request, Study $study): JsonResponse
    {
        $this->ensureAdmin($request);

        if ($study->medicalAttentions()->exists() || $study->medicalReports()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: el estudio tiene atenciones o informes asociados. Puede desactivarlo.',
            ], 422);
        }

        $study->delete();

        return response()->json([
            'message' => 'Estudio eliminado correctamente.',
        ]);
    }

    private function generateStudyCode(string $name): string
    {
        $base = Str::upper(Str::slug($name, '_'));
        $base = $base !== '' ? $base : 'ESTUDIO';
        $code = substr($base, 0, 40).'_'.Str::upper(Str::random(4));

        while (Study::where('code', $code)->exists()) {
            $code = substr($base, 0, 40).'_'.Str::upper(Str::random(4));
        }

        return $code;
    }

    private function ensureAdmin(Request $request): void
    {
        $user = $request->attributes->get('acting_user');
        if (! $user instanceof User || ! $user->isAdmin()) {
            throw new AccessDeniedHttpException('Solo el administrador puede gestionar el catálogo de estudios.');
        }
    }
}
