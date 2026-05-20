<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        Specialty::updateOrCreate(
            ['name' => 'Imagenología'],
            [
                'description' => 'Estudios de imagen diagnóstica general.',
                'status' => 'active',
            ],
        );

        Specialty::updateOrCreate(
            ['name' => 'Radiología'],
            [
                'description' => 'Estudios radiológicos convencionales.',
                'status' => 'active',
            ],
        );
    }
}
