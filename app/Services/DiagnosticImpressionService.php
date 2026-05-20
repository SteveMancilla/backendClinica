<?php

namespace App\Services;

use App\Exceptions\OllamaException;
use App\Models\MedicalReport;
use App\Services\Ollama\MedicalDiagnosticPromptBuilder;
use App\Services\Ollama\OllamaClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DiagnosticImpressionService
{
    public function __construct(
        private readonly OllamaClient $ollama,
        private readonly MedicalDiagnosticPromptBuilder $promptBuilder,
    ) {}

    /**
     * @return array{
     *     impression: string,
     *     suggestions: list<string>,
     *     source: 'ollama'|'rules',
     *     model: string|null
     * }
     */
    public function generate(MedicalReport $report): array
    {
        $report->load(['sections', 'study', 'patient', 'reportTemplate']);

        $hasFindings = $report->sections->contains(
            fn ($section) => filled(trim((string) ($section->content ?? ''))),
        );

        if (! $hasFindings) {
            throw new \InvalidArgumentException(
                'Debe dictar o escribir hallazgos en al menos una sección antes de generar la impresión diagnóstica.',
            );
        }

        if (config('ollama.enabled')) {
            $ollamaResult = $this->tryOllama($report);
            if ($ollamaResult !== null) {
                return $ollamaResult;
            }
        }

        if (! config('ollama.rules_fallback')) {
            throw new OllamaException(
                'No se pudo generar la impresión con Ollama y el respaldo por reglas está deshabilitado.',
            );
        }

        return $this->generateWithRules($report);
    }

    /**
     * @return array{impression: string, suggestions: list<string>, source: 'ollama', model: string}|null
     */
    private function tryOllama(MedicalReport $report): ?array
    {
        $messages = $this->promptBuilder->buildMessages($report);
        $models = array_values(array_filter([
            (string) config('ollama.model'),
            (string) config('ollama.fallback_model'),
        ]));

        $lastError = null;

        foreach (array_unique($models) as $model) {
            if ($model === '') {
                continue;
            }

            try {
                $raw = $this->ollama->chat($messages, $model);
                $impression = $this->normalizeImpression($raw);
                $suggestions = $this->extractSuggestions($impression);

                return [
                    'impression' => $impression,
                    'suggestions' => $suggestions,
                    'source' => 'ollama',
                    'model' => $model,
                ];
            } catch (OllamaException $exception) {
                $lastError = $exception;
                Log::warning('Ollama model failed for diagnostic impression', [
                    'model' => $model,
                    'report_id' => $report->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if ($lastError) {
            Log::error('All Ollama models failed for diagnostic impression', [
                'report_id' => $report->id,
                'error' => $lastError->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * @return array{impression: string, suggestions: list<string>, source: 'rules', model: null}
     */
    private function generateWithRules(MedicalReport $report): array
    {
        $combined = $report->sections
            ->map(fn ($s) => trim((string) ($s->content ?? '')))
            ->filter()
            ->implode("\n");

        $text = Str::lower($combined);
        $suggestions = [];
        $studyCode = $report->study?->code ?? '';
        $isRx = Str::contains(Str::lower($studyCode), 'rx')
            || Str::contains(Str::lower((string) $report->study?->name), 'radiograf');

        if ($this->containsAny($text, ['litiasis', 'cálculo', 'calculo', 'imagen litiásica', 'imagen litiasica'])) {
            $suggestions[] = 'Colecistopatía litiásica.';
        }

        if (Str::contains($text, 'barro biliar')) {
            $suggestions[] = 'Barro biliar.';
        }

        if ($this->containsAny($text, ['esteatosis', 'ecogenicidad aumentada'])) {
            $suggestions[] = 'Signos ecográficos de esteatosis hepática.';
        }

        if (Str::contains($text, 'antro gástrico') && Str::contains($text, 'engrosado')) {
            $suggestions[] = 'Engrosamiento de antro gástrico, a correlacionar clínicamente.';
        }

        if (Str::contains($text, 'meteorismo')) {
            $suggestions[] = 'Meteorismo incrementado a nivel de marco colónico.';
        }

        if ($this->containsAny($text, ['dilatación', 'dilatacion']) && $this->containsAny($text, ['colédoco', 'coledoco'])) {
            $suggestions[] = 'Dilatación de colédoco, a correlacionar con estudios complementarios.';
        }

        if (Str::contains($text, 'cardiomegalia')) {
            $suggestions[] = 'Cardiomegalia.';
        }

        if (Str::contains($text, 'derrame pleural')) {
            $suggestions[] = 'Derrame pleural.';
        }

        if (Str::contains($text, 'consolidación') || Str::contains($text, 'consolidacion')) {
            $suggestions[] = 'Consolidación pulmonar, a correlacionar clínicamente.';
        }

        if ($suggestions === []) {
            $suggestions[] = $isRx
                ? 'RADIOGRAFÍA DE TÓRAX DENTRO DE LÍMITES NORMALES.'
                : 'Estudio sin hallazgos patológicos evidentes al momento de la evaluación.';
        }

        $impression = collect($suggestions)
            ->values()
            ->map(fn (string $item, int $index) => ($index + 1).'. '.$item)
            ->implode("\n");

        return [
            'impression' => $impression,
            'suggestions' => $suggestions,
            'source' => 'rules',
            'model' => null,
        ];
    }

    private function normalizeImpression(string $raw): string
    {
        $text = trim($raw);
        $text = preg_replace('/^```(?:\w+)?\s*/m', '', $text) ?? $text;
        $text = preg_replace('/```\s*$/m', '', $text) ?? $text;
        $text = trim($text);

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $items = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $line = preg_replace('/^[\-\*•]\s+/', '', $line) ?? $line;
            $line = preg_replace('/^\d+[\.\)]\s*/', '', $line) ?? $line;
            $line = trim($line);

            if ($line !== '') {
                $items[] = $line;
            }
        }

        if ($items === []) {
            return '1. '.$text;
        }

        return collect($items)
            ->values()
            ->map(fn (string $item, int $index) => ($index + 1).'. '.$item)
            ->implode("\n");
    }

    /**
     * @return list<string>
     */
    private function extractSuggestions(string $impression): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $impression) ?: [])
            ->map(fn (string $line) => trim(preg_replace('/^\d+[\.\)]\s*/', '', $line) ?? $line))
            ->filter()
            ->values()
            ->all();
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (Str::contains($haystack, Str::lower($needle))) {
                return true;
            }
        }

        return false;
    }
}
