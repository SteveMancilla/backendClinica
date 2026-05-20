<?php

namespace App\Support;

/**
 * Columnas permitidas al hacer eager load de Patient (evita errores por columnas inexistentes).
 */
final class PatientRelationColumns
{
    public const SUMMARY = 'id,full_name,dni,sex,age,origin';

    public const SUMMARY_WITH_PHONE = 'id,full_name,dni,sex,age,origin,phone';

    public const ORIGIN_ONLY = 'id,origin';
}
