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
        Schema::table('review_requests', function (Blueprint $table) {
            $table->foreignId('job_card_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('review_link', 500)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('scheduled_for')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('review_requests', function (Blueprint $table) {
            $table->dropForeign(['job_card_id']);
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['job_card_id', 'customer_id', 'review_link', 'status', 'scheduled_for', 'sent_at', 'clicked_at']);
        });
    }
};
