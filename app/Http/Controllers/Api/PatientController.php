<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StorePatientRequest;
use App\Models\Patient;
use App\Models\User;
use App\Services\PatientVisibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PatientController extends Controller
{
    public function __construct(
        private readonly PatientVisibilityService $patientVisibility,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Patient::query()->orderBy('full_name');

        if ($actor = $this->actingUser($request)) {
            $this->patientVisibility->scopeVisibleTo($query, $actor);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'ilike', "%{$search}%")
                    ->orWhere('dni', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $actor = $this->actingUser($request);
        if (! $actor) {
            throw new AccessDeniedHttpException('Se requiere usuario autenticado para registrar pacientes.');
        }

        $data = $this->patientVisibility->applyRegistrationMeta($request->validated(), $actor);
        $patient = Patient::create($data);

        return response()->json([
            'message' => 'Paciente registrado correctamente.',
            'data' => $patient->load(['registeredBy:id,full_name,role', 'primaryDoctor:id,full_name']),
        ], 201);
    }

    public function show(Request $request, Patient $patient): JsonResponse
    {
        if ($actor = $this->actingUser($request)) {
            $visible = Patient::query()->whereKey($patient->id);
            $this->patientVisibility->scopeVisibleTo($visible, $actor);
            if (! $visible->exists()) {
                throw new AccessDeniedHttpException('No tienes permiso para ver este paciente.');
            }
        }

        return response()->json([
            'data' => $patient->load([
                'medicalAttentions.study',
                'medicalReports.study',
                'registeredBy:id,full_name,role',
                'primaryDoctor:id,full_name',
            ]),
        ]);
    }

    public function update(StorePatientRequest $request, Patient $patient): JsonResponse
    {
        $patient->update($request->validated());

        return response()->json([
            'message' => 'Paciente actualizado correctamente.',
            'data' => $patient->fresh(),
        ]);
    }

    private function actingUser(Request $request): ?User
    {
        $user = $request->attributes->get('acting_user');

        return $user instanceof User ? $user : null;
    }
}
