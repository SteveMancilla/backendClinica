<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@clinica.com'],
            [
                'dni' => '10000001',
                'full_name' => 'Administrador Clínica',
                'password' => 'admin123',
                'phone' => '987654321',
                'address' => 'Jr. Huancas 269, Huancayo',
                'role' => 'admin',
                'status' => 'active',
            ],
        );

        $doctorErlis = User::updateOrCreate(
            ['email' => 'doctor1@clinica.com'],
            [
                'dni' => '41234567',
                'full_name' => 'Dr. Erlis Arellano Cajachagua',
                'password' => 'doctor123',
                'phone' => '912345678',
                'address' => 'Jr. Huancas 269, Huancayo',
                'role' => 'doctor',
                'status' => 'active',
                'specialty' => 'Médico Radiólogo',
                'cmp' => '68711',
                'rne' => 'E/T',
            ],
        );

        $doctorElena = User::updateOrCreate(
            ['email' => 'doctor2@clinica.com'],
            [
                'dni' => '42345678',
                'full_name' => 'Dra. Elena Mendoza',
                'password' => 'doctor123',
                'phone' => '923456789',
                'role' => 'doctor',
                'status' => 'active',
                'specialty' => 'Imagenología',
                'cmp' => '51234',
            ],
        );

        User::updateOrCreate(
            ['email' => 'asistente1@clinica.com'],
            [
                'dni' => '72345678',
                'full_name' => 'Ana Ramírez',
                'password' => 'asistente123',
                'phone' => '956781234',
                'role' => 'assistant',
                'status' => 'active',
                'position' => 'Asistente médico',
                'associated_doctor_id' => $doctorErlis->id,
            ],
        );

        User::updateOrCreate(
            ['email' => 'asistente2@clinica.com'],
            [
                'dni' => '73456789',
                'full_name' => 'Luis Torres',
                'password' => 'asistente123',
                'phone' => '967891234',
                'role' => 'assistant',
                'status' => 'active',
                'position' => 'Asistente médico',
                'associated_doctor_id' => $doctorElena->id,
            ],
        );
    }
}
