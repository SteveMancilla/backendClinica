<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use App\Models\ReportTemplateSection;
use App\Models\Study;
use Illuminate\Database\Seeder;

class ReportTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $ecoStudy = Study::where('code', 'ECO_ABDOMEN_SUPERIOR')->firstOrFail();
        $rxStudy = Study::where('code', 'RX_TORAX')->firstOrFail();

        $ecoTemplate = ReportTemplate::updateOrCreate(
            ['code' => 'tpl_eco_abdomen_superior'],
            [
                'study_id' => $ecoStudy->id,
                'name' => 'Ecografía abdomen superior',
                'format_type' => 'structured',
                'description' => 'Plantilla estructurada por órganos — ecografía de abdomen superior.',
                'status' => 'active',
                'version' => 1,
            ],
        );

        $ecoSections = [
            [
                'title' => 'Hígado',
                'base_text' => 'Hígado de morfología conservada, con bordes lisos, ángulos agudos. Lóbulo hepático derecho ___ mm y lóbulo hepático izquierdo ___ mm. Parénquima homogéneo, sin lesiones focales. Ecogenicidad conservada. Vías biliares intrahepáticas no dilatadas. Vena porta de ___ mm de diámetro, sin evidenciarse trombos.',
            ],
            [
                'title' => 'Vesícula biliar',
                'base_text' => 'Vesícula biliar piriforme, de ___ x ___ mm, con pared de hasta ___ mm, con contenido homogéneo, anecogénico, sin evidencia de cálculos, pólipos ni barro biliar.',
            ],
            [
                'title' => 'Colédoco',
                'base_text' => 'Colédoco mide hasta ___ mm en segmento proximal, no se evidencian cálculos en su interior. Segmento distal no evaluable.',
            ],
            [
                'title' => 'Bazo',
                'base_text' => 'Bazo de morfología conservada, con bordes lisos, parénquima homogéneo, sin lesiones focales, ecogenicidad conservada. Dimensiones: ___ mm en diámetro longitudinal y ___ mm en diámetro transversal.',
            ],
            [
                'title' => 'Páncreas',
                'base_text' => 'Páncreas de morfología conservada, parénquima homogéneo, sin lesiones focales, ecogenicidad conservada. Cabeza de ___ mm, cuerpo de ___ mm y cola de ___ mm.',
            ],
            [
                'title' => 'Antro gástrico',
                'base_text' => 'Antro gástrico de espesor conservado. Mide hasta ___ mm.',
            ],
            [
                'title' => 'Asas intestinales',
                'base_text' => 'No se evidencian dilataciones patológicas, con peristaltismo conservado.',
            ],
            [
                'title' => 'Otros',
                'base_text' => 'No masas, no colecciones patológicas.',
            ],
        ];

        foreach ($ecoSections as $index => $section) {
            ReportTemplateSection::updateOrCreate(
                [
                    'report_template_id' => $ecoTemplate->id,
                    'title' => $section['title'],
                ],
                [
                    'order_index' => $index + 1,
                    'base_text' => $section['base_text'],
                    'is_required' => true,
                    'voice_enabled' => true,
                ],
            );
        }

        $rxTemplate = ReportTemplate::updateOrCreate(
            ['code' => 'tpl_rx_torax'],
            [
                'study_id' => $rxStudy->id,
                'name' => 'Radiografía de tórax',
                'format_type' => 'narrative',
                'description' => 'Plantilla narrativa para radiografía de tórax en proyección frontal.',
                'status' => 'active',
                'version' => 1,
            ],
        );

        ReportTemplateSection::updateOrCreate(
            [
                'report_template_id' => $rxTemplate->id,
                'title' => 'Hallazgos radiográficos',
            ],
            [
                'order_index' => 1,
                'base_text' => "RADIOGRAFÍA DE TÓRAX EN PROYECCIÓN FRONTAL POSTEROANTERIOR, CENTRADA, EN ADECUADA INSPIRACIÓN, MUESTRA:\n"
                    ."• TRANSPARENCIA PULMONAR CONSERVADA, CON ADECUADA VENTILACIÓN, NO SE EVIDENCIA PATRÓN INTERSTICIAL, NI ALVEOLAR, NO LESIONES FOCALES.\n"
                    ."• MEDIASTINO Y GRANDES VASOS DE MORFOLOGÍA HABITUAL.\n"
                    ."• REGIÓN PERIHILIAR Y TRAMA BRONCOVASCULAR DE MORFOLOGÍA HABITUAL.\n"
                    ."• SENOS COSTODIAFRAGMÁTICOS LIBRES.\n"
                    ."• SENOS CARDIOFRÉNICOS LIBRES.\n"
                    ."• ESTRUCTURAS ÓSEAS VISIBLES DENTRO DE LÍMITES NORMALES.\n"
                    .'• PARTES BLANDAS DENTRO DE LÍMITES NORMALES.',
                'is_required' => true,
                'voice_enabled' => true,
            ],
        );
    }
}
