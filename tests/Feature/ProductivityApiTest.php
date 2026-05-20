<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductivityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_fetch_productivity_dataset(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $doctor = User::factory()->create(['role' => 'doctor', 'status' => 'active']);

        $response = $this->withHeader('X-User-Id', (string) $admin->id)
            ->getJson('/api/productivity');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'doctors',
                    'appointments',
                    'reports',
                    'studies',
                    'specialties',
                    'patients',
                ],
            ]);

        $doctorIds = collect($response->json('data.doctors'))->pluck('id')->map(fn ($id) => (string) $id);
        $this->assertTrue($doctorIds->contains((string) $admin->id));
        $this->assertTrue($doctorIds->contains((string) $doctor->id));
    }
}
