<?php

namespace App\Services\Ollama;

use App\Models\MedicalReport;

class MedicalDiagnosticPromptBuilder
{
    /**
     * @return list<array{role: string, content: string}>
     */
    public function buildMessages(MedicalReport $report): array
    {
        $report->loadMissing(['sections', 'study', 'patient', 'reportTemplate']);

        $studyName = $report->study?->name ?? 'Estudio de imagen';
        $templateName = $report->reportTemplate?->name ?? $studyName;
        $formatType = $report->reportTemplate?->format_type ?? 'structured';
        $patient = $report->patient;

        $patientLine = 'Paciente no especificado';
        if ($patient) {
            $parts = array_filter([
                $patient->full_name,
                $patient->sex ? 'sexo '.$patient->sex : null,
                $patient->age ? $patient->age.' años' : null,
            ]);
            $patientLine = implode(', ', $parts);
        }

        $findings = $this->formatFindings($report);

        $system = <<<'PROMPT'
Eres un médico radiólogo peruano especializado en diagnóstico por imágenes. Tu única tarea es redactar la IMPRESIÓN DIAGNÓSTICA (conclusión) de un informe, basándote EXCLUSIVAMENTE en los hallazgos dictados por el médico.

REGLAS OBLIGATORIAS:
- Responde SOLO con la impresión diagnóstica, sin saludos ni explicaciones adicionales.
- Usa terminología médica formal en español (Perú), como en informes de imagenología impresos.
- Formato: lista numerada (1. 2. 3. …). Cada ítem es una oración diagnóstica breve y precisa.
- NO inventes hallazgos, patologías ni mediciones que no estén en los hallazgos dictados.
- NO indiques tratamiento, medicación ni recomendaciones terapéuticas.
- NO repitas textualmente párrafos largos ni plantillas por órgano; sintetiza solo el diagnóstico.
- NO listes órganos normales uno por uno; si todo es normal, una sola línea con la fórmula estándar del estudio.
- Máximo 5 ítems numerados, cada uno con una frase diagnóstica corta.
- Si todos los hallazgos son normales, concluye en una línea con formulación estándar del estudio.
- Si hay hallazgos patológicos, enuméralos con términos como: colecistopatía litiásica, esteatosis hepática, dilatación de colédoco, engrosamiento de antro gástrico, barro biliar, cardiomegalia, derrame pleural, consolidación, etc.
- Ante contradicciones entre secciones, escribe: "Hallazgos discordantes; correlacionar clínicamente y revisar dictado."

EJEMPLOS DE ESTILO (solo referencia de formato, no copiar si no aplican):

Ecografía abdomen superior — normal:
1. Estudio ecográfico de abdomen superior sin hallazgos patológicos evidentes.

Ecografía — patológico:
1. Colecistopatía litiásica.
2. Signos ecográficos de esteatosis hepática.

Radiografía de tórax — normal:
1. RADIOGRAFÍA DE TÓRAX DENTRO DE LÍMITES NORMALES.

Radiografía de tórax — patológico:
1. Cardiomegalia.
2. Derrame pleural derecho de moderada cuantía.
PROMPT;

        $user = "ESTUDIO: {$studyName}\n";
        $user .= "PLANTILLA: {$templateName} (formato: {$formatType})\n";
        $user .= "PACIENTE: {$patientLine}\n\n";
        $user .= "HALLAZGOS DICTADOS POR EL MÉDICO:\n";
        $user .= $findings !== '' ? $findings : '(Sin hallazgos dictados aún — indique que no es posible generar impresión sin contenido.)';
        $user .= "\n\nRedacta la impresión diagnóstica numerada:";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
    }

    private function formatFindings(MedicalReport $report): string
    {
        return $report->sections
            ->sortBy('order_index')
            ->map(function ($section) {
                $title = trim((string) $section->title);
                // Solo hallazgos dictados/editados por el médico (no la plantilla base).
                $content = trim((string) ($section->content ?? ''));

                if ($content === '') {
                    return null;
                }

                return "【{$title}】\n{$content}";
            })
            ->filter()
            ->implode("\n\n");
    }
}
