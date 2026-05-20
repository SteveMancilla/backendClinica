<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_attentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assistant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('specialty_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_template_id')->constrained()->cascadeOnDelete();
            $table->date('attention_date');
            $table->time('attention_time');
            $table->string('origin');
            $table->text('reason')->nullable();
            $table->text('observations')->nullable();
            $table->string('status')->default('pending_study');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_attentions');
    }
};
