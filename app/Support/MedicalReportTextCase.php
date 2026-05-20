<?php

namespace App\Support;

/**
 * Reglas tipográficas para informes médicos impresos:
 * - Etiquetas y títulos: MAYÚSCULAS.
 * - Dictado (hallazgos, impresión): oración (minúsculas con mayúscula inicial tras punto).
 */
class MedicalReportTextCase
{
    public static function uppercase(?string $text): string
    {
        return mb_strtoupper(trim((string) $text), 'UTF-8');
    }

    /**
     * Convierte dictado a estilo oración, respetando saltos de línea y viñetas.
     */
    public static function sentenceCase(?string $text): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [$text];
        $formatted = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                $formatted[] = '';

                continue;
            }

            $prefix = '';

            if (preg_match('/^([•\-]\s*)(.*)$/u', $line, $matches)) {
                $prefix = $matches[1];
                $line = $matches[2];
            }

            $formatted[] = $prefix.self::sentenceCaseLine($line);
        }

        return implode("\n", $formatted);
    }

    /**
     * Primera letra en mayúscula y tras cada fin de oración (. ! ?).
     */
    public static function sentenceCaseLine(string $line): string
    {
        $line = trim($line);

        if ($line === '') {
            return '';
        }

        $lower = mb_strtolower($line, 'UTF-8');

        $cased = preg_replace_callback(
            '/(^|[.!?]\s+)(\p{L})/u',
            static fn (array $match): string => $match[1].mb_strtoupper($match[2], 'UTF-8'),
            $lower,
        );

        return $cased ?? $lower;
    }
}
