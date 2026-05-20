<?php

namespace App\Http\Controllers\Api;

use App\Models\MedicalAttention;
use App\Models\MedicalReport;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\Study;
use App\Models\User;
use App\Services\MedicalReportVisibilityService;
use App\Services\PatientVisibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductivityController extends Controller
{
    public function __construct(
        private readonly MedicalReportVisibilityService $reportVisibility,
        private readonly PatientVisibilityService $patientVisibility,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User|null $actor */
        $actor = $request->attributes->get('acting_user');

        $reportsQuery = MedicalReport::query()
            ->select([
                'id',
                'medical_attention_id',
                'patient_id',
                'doctor_id',
                'study_id',
                'status',
                'created_at',
                'updated_at',
            ])
            ->with('medicalAttention:id,attention_date')
            ->orderByDesc('updated_at');

        if ($actor instanceof User) {
            $this->reportVisibility->scopeVisibleTo($reportsQuery, $actor);
        }

        $attentionsQuery = MedicalAttention::query()
            ->select([
                'id',
                'patient_id',
                'doctor_id',
                'assistant_id',
                'specialty_id',
                'study_id',
                'attention_date',
                'attention_time',
                'origin',
                'status',
                'created_at',
            ])
            ->orderByDesc('attention_date');

        if ($actor instanceof User && $actor->isDoctor()) {
            $attentionsQuery->where('doctor_id', $actor->id);
        } elseif ($actor instanceof User && $actor->isAssistant() && $actor->associated_doctor_id) {
            $attentionsQuery->where('doctor_id', $actor->associated_doctor_id);
        }

        $reports = $reportsQuery->get();
        $attentions = $attentionsQuery->get();

        $patientIds = $attentions->pluck('patient_id')
            ->merge($reports->pluck('patient_id'))
            ->unique()
            ->filter()
            ->values();

        $patientsQuery = Patient::query()
            ->select(['id', 'origin'])
            ->whereIn('id', $patientIds);

        if ($actor instanceof User) {
            $this->patientVisibility->scopeVisibleTo($patientsQuery, $actor);
        }

        $patients = $patientIds->isEmpty()
            ? collect()
            : $patientsQuery->get();

        $studies = Study::query()
            ->select(['id', 'specialty_id', 'code', 'name', 'format_type', 'status'])
            ->with('specialty:id,name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $specialtyIds = $studies->pluck('specialty_id')->unique()->filter();
        $specialties = $specialtyIds->isEmpty()
            ? collect()
            : Specialty::query()
                ->select(['id', 'name', 'description', 'status', 'created_at', 'updated_at'])
                ->whereIn('id', $specialtyIds)
                ->get();

        $doctorsQuery = User::query()
            ->select(['id', 'full_name', 'specialty', 'position', 'cmp', 'rne', 'role', 'status'])
            ->whereIn('role', ['doctor', 'admin'])
            ->where('status', 'active')
            ->orderBy('full_name');

        if ($actor instanceof User && $actor->isDoctor()) {
            $doctorsQuery->where('id', $actor->id);
        }

        $doctors = $doctorsQuery->get();

        return response()->json([
            'data' => [
                'doctors' => $doctors,
                'appointments' => $attentions,
                'reports' => $reports,
                'studies' => $studies,
                'specialties' => $specialties,
                'patients' => $patients,
            ],
        ]);
    }
}
