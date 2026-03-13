<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->enum('reminder_type', ['time_based', 'mileage_based', 'both']);
            $table->string('service_type'); // e.g., 'oil_change', 'annual_service', 'mot'
            $table->integer('interval_months')->nullable(); // For time-based
            $table->integer('interval_miles')->nullable(); // For mileage-based
            $table->integer('last_service_mileage')->nullable();
            $table->date('last_service_date')->nullable();
            $table->integer('current_mileage')->nullable();
            $table->date('next_due_date')->nullable();
            $table->integer('next_due_mileage')->nullable();
            $table->boolean('is_due')->default(false);
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('last_reminder_sent')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('documentable_type'); // Vehicle, JobCard, Invoice, Customer
            $table->unsignedBigInteger('documentable_id');
            $table->string('document_type'); // insurance, service_record, photo, receipt, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size'); // in bytes
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['documentable_type', 'documentable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('service_reminders');
    }
};
