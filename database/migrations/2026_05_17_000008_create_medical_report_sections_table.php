<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_report_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_template_section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedInteger('order_index');
            $table->text('base_text')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_report_sections');
    }
};
