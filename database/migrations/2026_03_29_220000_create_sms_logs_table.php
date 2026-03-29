<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sms_logs')) {
            return;
        }

        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('template_key')->nullable();
            $table->string('audience', 20)->default('customer');
            $table->string('recipient', 60);
            $table->string('normalized_recipient', 60)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('sent');
            $table->string('provider', 30)->nullable();
            $table->string('provider_reference')->nullable();
            $table->text('error_message')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('template_key');
            $table->index('audience');
            $table->index('status');
            $table->index(['related_type', 'related_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
