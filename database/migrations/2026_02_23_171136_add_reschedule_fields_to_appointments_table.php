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
            $table->date('proposed_date')->nullable()->after('status');
            $table->string('proposed_time', 10)->nullable()->after('proposed_date');
            $table->string('reschedule_token', 64)->nullable()->unique()->after('proposed_time');
            $table->timestamp('reschedule_proposed_at')->nullable()->after('reschedule_token');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['proposed_date', 'proposed_time', 'reschedule_token', 'reschedule_proposed_at']);
        });
    }
};
