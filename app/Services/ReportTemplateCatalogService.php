<?php

namespace App\Services;

use App\Models\ReportTemplate;
use App\Models\ReportTemplateSection;
use Illuminate\Support\Str;

class ReportTemplateCatalogService
{
    /**
     * Solo una plantilla activa por estudio (la usada en nuevas atenciones).
     */
    public function setAsActiveTemplate(ReportTemplate $template): void
    {
        ReportTemplate::query()
            ->where('study_id', $template->study_id)
            ->where('id', '!=', $template->id)
            ->update(['status' => 'inactive']);

        if ($template->status !== 'active') {
            $template->update(['status' => 'active']);
        }
    }

    public function generateUniqueCode(string $baseName): string
    {
        $slug = Str::slug($baseName, '_');
        $slug = $slug !== '' ? $slug : 'plantilla';

        do {
            $code = 'tpl_'.$slug.'_'.Str::lower(Str::random(6));
        } while (ReportTemplate::where('code', $code)->exists());

        return $code;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sectionsInput
     */
    public function syncSections(ReportTemplate $template, array $sectionsInput): void
    {
        $keepIds = [];

        foreach ($sectionsInput as $index => $section) {
            $order = (int) ($section['order_index'] ?? $section['order'] ?? $index + 1);
            $payload = [
                'title' => (string) ($section['title'] ?? 'Sección'),
                'order_index' => $order,
                'base_text' => (string) ($section['base_text'] ?? $section['baseText'] ?? ''),
                'is_required' => (bool) ($section['is_required'] ?? $section['isRequired'] ?? true),
                'voice_enabled' => (bool) ($section['voice_enabled'] ?? $section['voiceEnabled'] ?? true),
            ];

            $sectionId = $section['id'] ?? null;
            if ($sectionId && $existing = $template->sections()->find($sectionId)) {
                $existing->update($payload);
                $keepIds[] = $existing->id;
                continue;
            }

            $created = $template->sections()->create($payload);
            $keepIds[] = $created->id;
        }

        if ($keepIds === []) {
            $template->sections()->delete();

            return;
        }

        $template->sections()->whereNotIn('id', $keepIds)->delete();
    }

    public function isComplete(ReportTemplate $template): bool
    {
        return $template->sections()->exists();
    }

    public function canDelete(ReportTemplate $template): bool
    {
        return ! $template->medicalReports()->exists()
            && ! $template->medicalAttentions()->exists();
    }
}
