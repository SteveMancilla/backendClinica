<?php

namespace App\Support;

/**
 * Relaciones estándar para el listado de informes (modo resumen vs completo).
 * La especialidad del estudio debe cargarse con "study.specialty", nunca como columna "specialty" en studies.
 */
final class MedicalReportIndexRelations
{
    /**
     * @return list<string>
     */
    public static function summary(): array
    {
        return [
            'patient:'.PatientRelationColumns::SUMMARY,
            'doctor:id,full_name,email,specialty,cmp,rne',
            'study:id,name,specialty_id',
            'study.specialty:id,name',
        ];
    }

    /**
     * @return list<string>
     */
    public static function full(): array
    {
        return [
            'patient',
            'doctor:id,full_name,email,specialty,cmp,rne',
            'study.specialty:id,name',
            'reportTemplate',
            'sections',
            'medicalAttention',
        ];
    }
}
