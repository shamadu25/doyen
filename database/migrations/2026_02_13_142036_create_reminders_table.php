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
        if (!Schema::hasTable('reminders')) {
            Schema::create('reminders', function (Blueprint $table) {
                $table->id();
                $table->morphs('remindable'); // appointment_id, mot_test_id, etc.
                $table->string('type'); // appointment, mot, service
                $table->string('channel'); // email, sms
                $table->string('recipient'); // email address or phone number
                $table->timestamp('sent_at');
                $table->timestamps();
                
                $table->index(['remindable_type', 'remindable_id', 'sent_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
