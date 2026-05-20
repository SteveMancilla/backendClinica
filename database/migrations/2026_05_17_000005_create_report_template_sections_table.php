<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_template_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_template_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('order_index');
            $table->text('base_text');
            $table->boolean('is_required')->default(true);
            $table->boolean('voice_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_template_sections');
    }
};
