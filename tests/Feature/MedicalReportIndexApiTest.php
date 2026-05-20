<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalReportIndexApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_index_summary_mode_uses_valid_study_columns(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->withHeader('X-User-Id', (string) $admin->id)
            ->getJson('/api/medical-reports')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_reports_index_full_mode(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->withHeader('X-User-Id', (string) $admin->id)
            ->getJson('/api/medical-reports?full=1')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }
}
