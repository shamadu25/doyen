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
            // Update existing rows with null or empty test_result to 'booked'
            \DB::statement("UPDATE mot_tests SET test_result = 'booked' WHERE test_result IS NULL OR test_result = ''");
            
            // Modify the column to have a default value
            $table->string('test_result')->default('booked')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mot_tests', function (Blueprint $table) {
            $table->string('test_result')->default(null)->change();
        });
    }
};
