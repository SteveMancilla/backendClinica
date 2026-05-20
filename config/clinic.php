<?php

return [

    'name' => env('CLINIC_NAME', 'Clínica'),
    'center_name' => env('CLINIC_CENTER_NAME', 'Centro especializado en imagenología'),
    'tagline' => env('CLINIC_TAGLINE', 'Un buen diagnóstico es el inicio de una vida saludable'),
    'address' => env('CLINIC_ADDRESS', 'Jr. Huancas N° 269 (esquina con Uruguay) – Huancayo'),
    'phone' => env('CLINIC_PHONE', '067-501000'),

    'pdf' => [
        'disk' => env('CLINIC_PDF_DISK', 'local'),
        'directory' => 'medical-reports',
    ],

];
