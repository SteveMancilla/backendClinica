<?php

namespace App\Http\Controllers\Api;

use App\Models\MedicalReport;
use App\Models\User;
use App\Services\MedicalReportVisibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private const ATTENTION_STATUSES = [
        'missing_report',
        'missing_diagnostic_impression',
        'in_review',
    ];

    public function __construct(
        private readonly MedicalReportVisibilityService $reportVisibility,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $limit = min(20, max(1, $request->integer('limit', 12)));

        $query = MedicalReport::query()
            ->select(['id', 'patient_id', 'doctor_id', 'study_id', 'status', 'updated_at'])
            ->with([
                'patient:id,full_name',
                'study:id,name',
                'doctor:id,full_name',
            ])
            ->whereIn('status', self::ATTENTION_STATUSES)
            ->orderByDesc('updated_at')
            ->limit($limit);

        /** @var User|null $actor */
        $actor = $request->attributes->get('acting_user');
        if ($actor instanceof User) {
            $this->reportVisibility->scopeVisibleTo($query, $actor);
        }

        $reports = $query->get();

        $items = $reports->map(static function (MedicalReport $report): array {
            return [
                'id' => $report->id,
                'patient_name' => $report->patient?->full_name,
                'study_name' => $report->study?->name,
                'doctor_name' => $report->doctor?->full_name,
                'status' => $report->status,
                'updated_at' => $report->updated_at,
            ];
        });

        $countQuery = MedicalReport::query()
            ->whereIn('status', self::ATTENTION_STATUSES);

        if ($actor instanceof User) {
            $this->reportVisibility->scopeVisibleTo($countQuery, $actor);
        }

        return response()->json([
            'data' => [
                'items' => $items,
                'unread_count' => $countQuery->count(),
            ],
        ]);
    }
}
