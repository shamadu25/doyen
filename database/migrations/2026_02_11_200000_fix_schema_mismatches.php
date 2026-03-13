<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add completion_date to job_cards
        Schema::table('job_cards', function (Blueprint $table) {
            $table->dateTime('completion_date')->nullable()->after('status');
        });

        // Make service_id nullable and add description to job_card_services
        Schema::table('job_card_services', function (Blueprint $table) {
            $table->text('description')->nullable()->after('service_id');
        });

        // We need to make service_id nullable - drop the foreign key first, modify column, re-add
        Schema::table('job_card_services', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->unsignedBigInteger('service_id')->nullable()->change();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropColumn('completion_date');
        });

        Schema::table('job_card_services', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('job_card_services', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->unsignedBigInteger('service_id')->nullable(false)->change();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }
};
