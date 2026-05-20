<?php

namespace App\Http\Controllers\Api;

use App\Support\PatientRelationColumns;
use App\Http\Requests\StoreMedicalAttentionRequest;
use App\Http\Resources\MedicalAttentionResource;
use App\Models\MedicalAttention;
use App\Models\Study;
use App\Models\User;
use App\Services\MedicalReportCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MedicalAttentionController extends Controller
{
    public function __construct(
        private readonly MedicalReportCreationService $reportCreationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $full = $request->boolean('full');

        $relations = $full
            ? [
                'patient',
                'doctor:id,full_name,email,specialty,role',
                'assistant:id,full_name,email,position,role',
                'specialty',
                'study',
                'reportTemplate',
                'medicalReport.sections',
            ]
            : [
                'patient:'.PatientRelationColumns::SUMMARY,
                'doctor:id,full_name,email,specialty,role',
                'specialty:id,name',
                'study:id,name,specialty_id',
            ];

        $query = MedicalAttention::with($relations)->latest();

        if ($patientId = $request->integer('patient_id')) {
            $query->where('patient_id', $patientId);
        }

        if ($doctorId = $request->integer('doctor_id')) {
            $query->where('doctor_id', $doctorId);
        }

        if ($status = $request->string('status')->toString()) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        return response()->json([
            'data' => MedicalAttentionResource::collection($query->get()),
        ]);
    }

    public function store(StoreMedicalAttentionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $study = Study::with('specialty')->findOrFail($data['study_id']);

        if ($study->status !== 'active') {
            throw new UnprocessableEntityHttpException('El estudio seleccionado no está activo.');
        }

        $template = $study->activeReportTemplate();

        if (! $template) {
            throw new UnprocessableEntityHttpException(
                'No existe una plantilla activa para el estudio seleccionado.',
            );
        }

        $doctor = User::findOrFail($data['doctor_id']);
        if (! in_array($doctor->role, ['doctor', 'admin'], true)) {
            throw new UnprocessableEntityHttpException('El usuario seleccionado no es un médico válido.');
        }

        $attention = DB::transaction(function () use ($data, $study, $template) {
            $attention = MedicalAttention::create([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'assistant_id' => $data['assistant_id'] ?? null,
                'specialty_id' => $study->specialty_id,
                'study_id' => $study->id,
                'report_template_id' => $template->id,
                'attention_date' => $data['attention_date'],
                'attention_time' => $data['attention_time'],
                'origin' => $data['origin'],
                'reason' => $data['reason'] ?? null,
                'observations' => $data['observations'] ?? null,
                'status' => $data['status'] ?? 'pending_study',
                'created_by' => $data['created_by'] ?? null,
            ]);

            $report = $this->reportCreationService->createFromAttention($attention, $template);

            $attention->setRelation('medicalReport', $report);

            return $attention;
        });

        $attention->load([
            'patient',
            'doctor:id,full_name,email,specialty,role',
            'assistant:id,full_name,email,position,role',
            'specialty',
            'study',
            'reportTemplate',
            'medicalReport.sections',
        ]);

        return response()->json([
            'message' => 'Atención registrada correctamente.',
            'data' => new MedicalAttentionResource($attention),
        ], 201);
    }

    public function show(MedicalAttention $medicalAttention): JsonResponse
    {
        $medicalAttention->load([
            'patient',
            'doctor:id,full_name,email,specialty,role',
            'assistant:id,full_name,email,position,role',
            'specialty',
            'study',
            'reportTemplate.sections',
            'medicalReport.sections',
        ]);

        return response()->json([
            'data' => new MedicalAttentionResource($medicalAttention),
        ]);
    }
}
