<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studies', function (Blueprint $table) {
            if (! Schema::hasColumn('studies', 'block')) {
                $table->string('block')->default('Ecografía general')->after('name');
            }
            if (Schema::hasColumn('studies', 'subgroup')) {
                $table->dropColumn('subgroup');
            }
        });
    }

    public function down(): void
    {
        Schema::table('studies', function (Blueprint $table) {
            if (Schema::hasColumn('studies', 'block')) {
                $table->dropColumn(['block']);
            }
        });
    }
};
