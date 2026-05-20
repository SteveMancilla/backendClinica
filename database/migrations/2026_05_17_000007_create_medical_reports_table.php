<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_attention_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('study_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_template_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('missing_report');
            $table->text('diagnostic_impression')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('concluded_at')->nullable();
            $table->timestamp('generated_pdf_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_reports');
    }
};
