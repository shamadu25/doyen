<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mot_tests', function (Blueprint $table) {
            if (!Schema::hasColumn('mot_tests', 'certificate_path')) {
                $table->string('certificate_path')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mot_tests', function (Blueprint $table) {
            if (Schema::hasColumn('mot_tests', 'certificate_path')) {
                $table->dropColumn('certificate_path');
            }
        });
    }
};
