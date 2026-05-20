<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileUpdateApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function doctor_can_update_own_profile(): void
    {
        $doctor = User::factory()->create([
            'role' => 'doctor',
            'email' => 'doc@test.com',
            'password' => 'secret12',
            'phone' => '900000001',
        ]);

        $response = $this->withHeader('X-User-Id', (string) $doctor->id)
            ->putJson('/api/auth/profile', [
                'full_name' => 'Dr. Nombre Actualizado',
                'phone' => '911111111',
                'specialty' => 'Radiología',
            ]);

        $response->assertOk();

        $doctor->refresh();
        $this->assertSame('Dr. Nombre Actualizado', $doctor->full_name);
        $this->assertSame('911111111', $doctor->phone);
        $this->assertSame('Radiología', $doctor->specialty);
    }
}
