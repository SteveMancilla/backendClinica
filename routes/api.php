<?php

use App\Http\Controllers\Api\AiHealthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MedicalAttentionController;
use App\Http\Controllers\Api\MedicalReportController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\ProductivityController;
use App\Http\Controllers\Api\ReportTemplateController;
use App\Http\Controllers\Api\SpecialtyController;
use App\Http\Controllers\Api\StudyController;
use App\Http\Controllers\Api\SystemSettingsController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\ResolveActingUser;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API — Clínica Zárate
|--------------------------------------------------------------------------
|
| Rutas consumidas por el frontend React. Prefijo automático: /api
| Sin autenticación por ahora (Sanctum en fase posterior).
|
*/

Route::get('/health', HealthController::class);
Route::get('/ai/health', AiHealthController::class);

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(ResolveActingUser::class)->group(function () {
    Route::put('/auth/password', [AuthController::class, 'changePassword']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::apiResource('patients', PatientController::class);
    Route::get('/specialties', [SpecialtyController::class, 'index']);
    Route::post('/report-templates/restore-defaults', [ReportTemplateController::class, 'restoreDefaults']);
    Route::apiResource('studies', StudyController::class);
    Route::apiResource('report-templates', ReportTemplateController::class);
    Route::apiResource('medical-attentions', MedicalAttentionController::class)->only(['index', 'store', 'show']);
    Route::apiResource('medical-reports', MedicalReportController::class)->only(['index', 'show', 'update']);

    Route::post('/medical-reports/{medical_report}/generate-diagnostic-impression', [
        MedicalReportController::class,
        'generateDiagnosticImpression',
    ]);
    Route::post('/medical-reports/{medical_report}/conclude', [
        MedicalReportController::class,
        'conclude',
    ]);
    Route::post('/medical-reports/{medical_report}/generate-pdf', [
        MedicalReportController::class,
        'generatePdf',
    ]);
    Route::get('/medical-reports/{medical_report}/pdf', [
        MedicalReportController::class,
        'downloadPdf',
    ]);

    Route::get('/productivity', [ProductivityController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);

    Route::get('/settings', [SystemSettingsController::class, 'show']);
    Route::put('/settings', [SystemSettingsController::class, 'update']);
});
