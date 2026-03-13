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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('cancellation_reason')->nullable()->after('status');
            $table->date('reschedule_requested_date')->nullable()->after('proposed_time');
            $table->string('reschedule_requested_time', 10)->nullable()->after('reschedule_requested_date');
            $table->text('reschedule_notes')->nullable()->after('reschedule_requested_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'reschedule_requested_date', 'reschedule_requested_time', 'reschedule_notes']);
        });
    }
};
