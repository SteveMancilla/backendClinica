<?php

namespace Database\Seeders;

use App\Models\Specialty;
use App\Models\Study;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudySeeder extends Seeder
{
    public function run(): void
    {
        $imagenologia = Specialty::where('name', 'Imagenología')->firstOrFail();
        $radiologia = Specialty::where('name', 'Radiología')->firstOrFail();

        $this->seedGroup($imagenologia->id, 'Ecografía general', 'structured', [
            'Ecografía abdomen completo',
            'Ecografía abdomen superior',
            'Ecografía abdomen inferior',
            'Ecografía renal',
            'Ecografía vesical',
            'Ecografía prostático',
            'Ecografía reno-vesical',
            'Ecografía reno-vesico-prostático',
            'Ecografía vesico-prostático',
            'Ecografía pélvica',
            'Ecografía transvaginal',
            'Ecografía de barrido pulmonar',
            'Ecografía pylorica / neonatos',
        ]);

        $this->seedGroup($imagenologia->id, 'Ecografía de partes blandas', 'structured', [
            'Ecografía de partes blandas',
            'Ecografía de mamas',
            'Ecografía de tiroides',
            'Ecografía de parótidas',
            'Ecografía de cabeza',
            'Ecografía de cara',
            'Ecografía de cuello',
            'Ecografía cervical',
            'Ecografía de pared abdominal',
            'Ecografía testicular',
            'Ecografía de lesiones superficiales / piel',
            'Ecografía inguinal unilateral',
            'Ecografía inguinal bilateral',
            'Ecografía umbilical',
            'Ecografía de glándulas salivales',
            'Ecografía transfontanelar / cabeza niños',
            'Ecografía de pene',
            'Ecografía ocular',
        ]);

        $this->seedGroup($imagenologia->id, 'Ecografía articular', 'structured', [
            'Ecografía de tendones y/o ligamentos',
            'Ecografía de rodilla unilateral',
            'Ecografía de rodilla bilateral',
            'Ecografía de hombro unilateral',
            'Ecografía de hombro bilateral',
            'Ecografía de codo unilateral',
            'Ecografía de codo bilateral',
            'Ecografía de tobillo unilateral',
            'Ecografía de tobillo bilateral',
            'Ecografía de muñeca unilateral',
            'Ecografía de muñeca bilateral',
            'Ecografía de mano unilateral',
            'Ecografía de mano bilateral',
            'Ecografía de pie unilateral',
            'Ecografía de pie bilateral',
            'Ecografía de dedo / pie o mano',
            'Ecografía de cadera unilateral',
            'Ecografía de cadera bilateral',
            'Ecografía de cadera niños / displasia',
        ]);

        $this->seedGroup($imagenologia->id, 'Ecografía Doppler', 'structured', [
            'Ecografía Doppler venoso miembros inferiores unilateral',
            'Ecografía Doppler venoso miembros inferiores bilateral',
            'Ecografía Doppler arterial miembros inferiores unilateral',
            'Ecografía Doppler arterial miembros inferiores bilateral',
            'Ecografía Doppler venoso miembros superiores unilateral',
            'Ecografía Doppler venoso miembros superiores bilateral',
            'Ecografía Doppler arterial miembros superiores unilateral',
            'Ecografía Doppler arterial miembros superiores bilateral',
            'Ecografía Doppler portal / espleno portal',
            'Ecografía Doppler carotídeo vertebro basilar',
            'Ecografía Doppler renal',
            'Ecografía Doppler testicular',
            'Ecografía Doppler pene',
            'Ecografía Doppler tiroideo',
            'Ecografía Doppler aórtico abdominal',
        ]);

        $this->seedGroup($imagenologia->id, 'Elastografías', 'structured', [
            'Elastografía cualitativa / partes blandas',
            'Elastografía cuantitativa hepática',
            'Elastografía cuantitativa mamas',
            'Elastografía cuantitativa tiroidea',
        ]);

        $this->seedGroup($imagenologia->id, 'Procedimientos', 'structured', [
            'Toracocentesis ecoguiada sin materiales',
            'Toracocentesis ecoguiada con materiales',
            'Paracentesis ecoguiada sin materiales',
            'Paracentesis ecoguiada con materiales',
            'Drenajes de partes blandas ecoguiados sin materiales',
            'Drenajes de partes blandas ecoguiados con materiales',
        ]);

        $this->seedGroup($imagenologia->id, 'Biopsias', 'structured', [
            'Biopsia con trucut sin materiales',
            'Biopsia con trucut con materiales',
            'Biopsia con trucut con materiales e informe patológico',
            'Biopsia BAAF o PAAF sin materiales',
            'Biopsia BAAF o PAAF con materiales',
            'Biopsia BAAF o PAAF con materiales e informe patológico',
            'Punción aspirativa sin materiales',
            'Punción aspirativa con materiales',
            'Punción aspirativa con materiales e informe patológico',
            'Consulta radiológica para procedimientos complejos',
        ]);

        $this->seedGroup($radiologia->id, 'Radiografías domiciliarias', 'narrative', [
            'Radiografía de tórax frontal',
            'Radiografía de tórax lateral',
            'Radiografía de tórax frontal y lateral',
            'Radiografía de abdomen de pie',
            'Radiografía de abdomen decúbito',
            'Radiografía de cráneo',
            'Radiografía de huesos propios de la nariz',
            'Radiografía de articulación temporomandibular unilateral',
            'Radiografía de articulación temporomandibular bilateral',
            'Radiografía de mastoides unilateral',
            'Radiografía de mastoides bilateral',
            'Radiografía silla turca',
            'Radiografía de mandíbula',
            'Radiografía maxilar',
            'Radiografía senos paranasales',
            'Radiografía de cavum faringe boca abierta / cerrada',
            'Radiografía de columna cervical',
            'Radiografía de columna cervical funcional',
            'Radiografía de columna dorsal',
            'Radiografía de columna dorsal funcional',
            'Radiografía de columna dorsolumbar',
            'Radiografía de columna lumbar',
            'Radiografía de columna lumbar funcional',
            'Radiografía de columna lumbosacra',
            'Radiografía de columna sacrocoxis',
            'Radiografía de columna coxis',
            'Radiografía de clavícula unilateral',
            'Radiografía de clavícula bilateral',
            'Radiografía de hombro unilateral',
            'Radiografía de hombro bilateral',
            'Radiografía de hombro transtoráxica unilateral',
            'Radiografía de hombro transtoráxica bilateral',
            'Radiografía escapular unilateral',
            'Radiografía escapular bilateral',
            'Radiografía de brazo / húmero unilateral',
            'Radiografía de brazo / húmero bilateral',
            'Radiografía de codo unilateral',
            'Radiografía de codo bilateral',
            'Radiografía de antebrazo / radio y cúbito unilateral',
            'Radiografía de antebrazo / radio y cúbito bilateral',
            'Radiografía de muñeca unilateral',
            'Radiografía de muñeca bilateral',
            'Radiografía de mano unilateral',
            'Radiografía de mano bilateral',
            'Radiografía de pelvis frontal',
            'Radiografía de pelvis frontal, inlet, outlet',
            'Radiografía de cadera adulto unilateral',
            'Radiografía de cadera adulto bilateral',
            'Radiografía de cadera niño / C/C displasia',
            'Radiografía de fémur unilateral',
            'Radiografía de fémur bilateral',
            'Radiografía rodilla unilateral',
            'Radiografía rodilla bilateral',
            'Radiografía de rótula axial unilateral',
            'Radiografía de rótula axial bilateral',
            'Radiografía de pierna / tibia peroné unilateral',
            'Radiografía de pierna / tibia peroné bilateral',
            'Radiografía de tobillo unilateral',
            'Radiografía de tobillo bilateral',
            'Radiografía de pie unilateral',
            'Radiografía de pie bilateral',
            'Radiografía de calcáneo unilateral',
            'Radiografía de calcáneo bilateral',
            'Radiografía de mensuración de MMII',
            'Radiografía de edad ósea mano izquierda',
            'Radiografía de parrilla costal unilateral',
            'Radiografía de parrilla costal bilateral',
        ]);
    }

    /**
     * @param array<int, string> $studyNames
     */
    private function seedGroup(
        int $specialtyId,
        string $group,
        string $formatType,
        array $studyNames,
    ): void {
        foreach ($studyNames as $name) {
            Study::updateOrCreate(
                ['code' => $this->buildCode($group, $name)],
                [
                    'specialty_id' => $specialtyId,
                    'name' => $name,
                    'block' => $group,
                    'format_type' => $formatType,
                    'status' => 'active',
                ],
            );
        }
    }

    private function buildCode(string $group, string $name): string
    {
        if (mb_strtolower($name) === 'ecografía abdomen superior') {
            return 'ECO_ABDOMEN_SUPERIOR';
        }
        if (mb_strtolower($name) === 'radiografía de tórax frontal') {
            return 'RX_TORAX';
        }

        $prefix = $group === 'Radiografías domiciliarias' ? 'RX' : 'ECO';
        $slug = Str::upper(Str::slug($name, '_'));
        $slug = Str::limit($slug, 60, '');

        return $prefix.'_'.$slug;
    }
}
