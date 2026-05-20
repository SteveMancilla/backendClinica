<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        Patient::updateOrCreate(
            ['dni' => '45678912'],
            [
                'full_name' => 'Quispe Huamán Yaquelin',
                'age' => 22,
                'sex' => 'Femenino',
                'phone' => '987654321',
                'origin' => 'Particular',
                'status' => 'active',
            ],
        );

        Patient::updateOrCreate(
            ['dni' => '70123456'],
            [
                'full_name' => 'Paciente Demo Abdomen',
                'age' => 45,
                'sex' => 'Masculino',
                'phone' => '912345678',
                'origin' => 'Consulta externa',
                'status' => 'active',
                'notes' => 'Paciente ficticio para pruebas de ecografía abdominal.',
            ],
        );
    }
}
