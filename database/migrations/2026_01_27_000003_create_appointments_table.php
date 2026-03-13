<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('scheduled_date');
            $table->dateTime('scheduled_end_date')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->string('appointment_type'); // service, mot, repair, diagnosis
            $table->string('status')->default('scheduled'); // scheduled, confirmed, in_progress, completed, cancelled, no_show
            $table->text('description')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('reminder_sent')->default('no'); // no, email, sms, both
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['scheduled_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
