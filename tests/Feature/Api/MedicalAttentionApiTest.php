<?php

namespace Tests\Feature\Api;

use App\Models\Patient;
use App\Models\Study;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalAttentionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_medical_attention_with_report_sections(): void
    {
        $this->seed();

        $patient = Patient::first();
        $doctor = User::where('role', 'doctor')->first();
        $study = Study::where('code', 'ECO_ABDOMEN_SUPERIOR')->first();

        $response = $this->postJson('/api/medical-attentions', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'study_id' => $study->id,
            'attention_date' => '2026-05-17',
            'attention_time' => '09:00',
            'origin' => 'Particular',
            'reason' => 'Control abdominal',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Atención registrada correctamente.')
            ->assertJsonPath('data.study.code', 'ECO_ABDOMEN_SUPERIOR');

        $this->assertDatabaseHas('medical_attentions', [
            'patient_id' => $patient->id,
            'study_id' => $study->id,
        ]);

        $this->assertDatabaseCount('medical_report_sections', 8);
    }
}
