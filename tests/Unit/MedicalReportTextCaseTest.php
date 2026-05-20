<?php

namespace Tests\Unit;

use App\Support\MedicalReportTextCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MedicalReportTextCaseTest extends TestCase
{
    #[Test]
    public function it_formats_dictation_in_sentence_case(): void
    {
        $input = 'TRANSPARENCIA PULMONAR CONSERVADA. NO DERRAME PLEURAL.';

        $this->assertSame(
            'Transparencia pulmonar conservada. No derrame pleural.',
            MedicalReportTextCase::sentenceCase($input),
        );
    }

    #[Test]
    public function it_preserves_bullet_prefixes(): void
    {
        $input = "- HÍGADO DE MORFOLOGÍA CONSERVADA.\n- BAZO NORMAL.";

        $this->assertSame(
            "- Hígado de morfología conservada.\n- Bazo normal.",
            MedicalReportTextCase::sentenceCase($input),
        );
    }

    #[Test]
    public function it_uppercases_labels(): void
    {
        $this->assertSame('HÍGADO', MedicalReportTextCase::uppercase('hígado'));
    }
}
