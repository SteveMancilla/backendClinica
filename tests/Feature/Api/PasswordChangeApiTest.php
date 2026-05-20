<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordChangeApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_reset_doctor_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
        ]);

        $doctor = User::factory()->create([
            'role' => 'doctor',
            'email' => 'doctor@test.com',
            'password' => 'oldpass',
        ]);

        $response = $this->withHeader('X-User-Id', (string) $admin->id)
            ->putJson("/api/users/{$doctor->id}", [
                'password' => 'newpass123',
            ]);

        $response->assertOk();

        $doctor->refresh();
        $this->assertTrue(Hash::check('newpass123', $doctor->password));
    }

    #[Test]
    public function user_can_change_own_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'role' => 'doctor',
            'email' => 'doc@test.com',
            'password' => 'secret12',
        ]);

        $response = $this->withHeader('X-User-Id', (string) $user->id)
            ->putJson('/api/auth/password', [
                'current_password' => 'secret12',
                'password' => 'nueva123',
                'password_confirmation' => 'nueva123',
            ]);

        $response->assertOk();

        $user->refresh();
        $this->assertTrue(Hash::check('nueva123', $user->password));
    }
}
