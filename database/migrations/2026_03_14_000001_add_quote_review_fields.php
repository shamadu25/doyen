<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('review_token', 64)->unique()->nullable()->after('status');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete()->after('vehicle_id');
        });

        // Add pending_quote status to appointments if using string column
        // (no enum change needed as appointments.status is a string column)
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn(['review_token', 'appointment_id']);
        });
    }
};
