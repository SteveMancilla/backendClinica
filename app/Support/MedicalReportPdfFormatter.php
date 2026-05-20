<?php

namespace App\Support;

use App\Models\MedicalReport;
use App\Models\User;
use Carbon\Carbon;

class MedicalReportPdfFormatter
{
    /**
     * Médico que firma el informe: médico de la atención, médico asociado del asistente o admin.
     */
    public static function resolveReportingPhysician(MedicalReport $report): ?User
    {
        $report->loadMissing([
            'doctor',
            'medicalAttention.doctor',
            'medicalAttention.assistant.associatedDoctor',
        ]);

        $attentionDoctor = $report->medicalAttention?->doctor;
        if ($attentionDoctor && in_array($attentionDoctor->role, ['doctor', 'admin'], true)) {
            return $attentionDoctor;
        }

        $assigned = $report->doctor;

        if ($assigned?->role === 'assistant') {
            $associated = $report->medicalAttention?->assistant?->associatedDoctor
                ?? $assigned->associatedDoctor;

            if ($associated && in_array($associated->role, ['doctor', 'admin'], true)) {
                return $associated;
            }
        }

        if ($assigned && in_array($assigned->role, ['doctor', 'admin'], true)) {
            return $assigned;
        }

        $associated = $report->medicalAttention?->assistant?->associatedDoctor;
        if ($associated) {
            return $associated;
        }

        return $assigned;
    }

    public static function honorificDoctorName(?string $fullName): string
    {
        if (! filled($fullName)) {
            return '—';
        }

        $clean = trim(preg_replace(
            '/^(Dr\.?|Dra\.?|Dr\(a\)\.?|DR\.?|DRA\.?)\s+/iu',
            '',
            trim($fullName),
        ) ?? trim($fullName));

        return 'Dr(a). '.mb_strtoupper($clean, 'UTF-8');
    }

    public static function formatPhysicianTitle(?User $user): string
    {
        if (! $user) {
            return '—';
        }

        if ($user->role === 'admin' && filled($user->position)) {
            return mb_strtoupper(trim((string) $user->position), 'UTF-8');
        }

        if (filled($user->specialty)) {
            return mb_strtoupper(trim((string) $user->specialty), 'UTF-8');
        }

        if (filled($user->position) && $user->role === 'doctor') {
            return mb_strtoupper(trim((string) $user->position), 'UTF-8');
        }

        return '—';
    }

    /**
     * @return array{name: string, specialty: string}
     */
    public static function formatPhysicianBlock(?User $doctor): array
    {
        if (! $doctor) {
            return [
                'name' => '—',
                'specialty' => '—',
            ];
        }

        return [
            'name' => self::honorificDoctorName($doctor->full_name),
            'specialty' => self::formatPhysicianTitle($doctor),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function formatReportingPhysicianPayload(?User $doctor): ?array
    {
        if (! $doctor) {
            return null;
        }

        $block = self::formatPhysicianBlock($doctor);

        return [
            'id' => $doctor->id,
            'full_name' => $doctor->full_name,
            'role' => $doctor->role,
            'specialty' => $doctor->specialty,
            'position' => $doctor->position,
            'honorific_name' => $block['name'],
            'title' => $block['specialty'],
        ];
    }

    public static function formatAttentionDateTime(MedicalReport $report): string
    {
        $attention = $report->medicalAttention;
        $date = $attention?->attention_date ?? $report->created_at;

        if (! $date) {
            return '—';
        }

        Carbon::setLocale('es');
        $carbon = Carbon::parse($date);

        $time = $attention?->attention_time
            ? substr((string) $attention->attention_time, 0, 5)
            : ($report->created_at?->format('H:i') ?? '');

        $formatted = ucfirst($carbon->translatedFormat('l, j \d\e F \d\e Y'));

        return $time !== ''
            ? "{$formatted} {$time} Horas"
            : $formatted;
    }

    public static function uppercase(?string $text): string
    {
        return MedicalReportTextCase::uppercase($text);
    }

    /**
     * @return list<array{title: string, content: string}>
     */
    public static function formatSections(MedicalReport $report, string $formatType): array
    {
        return $report->sections
            ->sortBy('order_index')
            ->map(function ($section) use ($formatType) {
                $content = trim((string) ($section->content ?? ''));

                if ($content === '') {
                    return null;
                }

                $title = self::uppercase((string) $section->title);

                if ($formatType === 'narrative') {
                    $narrative = self::formatNarrativeSection($content);

                    return [
                        'title' => $title,
                        'intro' => $narrative['intro'],
                        'content' => $narrative['body'],
                        'is_narrative' => true,
                    ];
                }

                return [
                    'title' => $title,
                    'content' => MedicalReportTextCase::sentenceCase($content),
                    'is_narrative' => false,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{intro: string, body: string}
     */
    public static function formatNarrativeSection(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $intro = '';
        $bodyLines = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $isIntro = $index === 0
                || str_contains(mb_strtoupper($line, 'UTF-8'), 'MUESTRA:');

            if ($isIntro && $intro === '') {
                $intro = self::uppercase($line);

                continue;
            }

            $line = preg_replace('/^[•\-]\s*/u', '', $line) ?? $line;
            $bodyLines[] = '- '.MedicalReportTextCase::sentenceCaseLine($line);
        }

        if ($intro === '' && $bodyLines !== []) {
            $intro = array_shift($bodyLines) ?? '';
            $intro = ltrim($intro, '- ');
        }

        return [
            'intro' => $intro,
            'body' => implode("\n", $bodyLines),
        ];
    }

    /**
     * @return list<array{number: int, text: string}>
     */
    public static function formatDiagnosticImpression(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($raw)) ?: [];
        $items = [];

        foreach ($lines as $line) {
            $line = trim(preg_replace('/^\d+[\.\)]\s*/', '', trim($line)) ?? trim($line));
            if ($line !== '') {
                $items[] = MedicalReportTextCase::sentenceCaseLine($line);
            }
        }

        if ($items === []) {
            return [];
        }

        return collect($items)
            ->values()
            ->map(fn (string $text, int $index) => [
                'number' => $index + 1,
                'text' => $text,
            ])
            ->all();
    }
}
