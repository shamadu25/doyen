<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_cards', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('mileage_in')->nullable();
            $table->integer('mileage_out')->nullable();
            $table->dateTime('date_in');
            $table->dateTime('date_out')->nullable();
            $table->dateTime('promised_date')->nullable();
            $table->string('status')->default('open'); // open, in_progress, awaiting_parts, awaiting_approval, completed, invoiced
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->text('customer_complaint')->nullable();
            $table->text('work_required')->nullable();
            $table->text('work_completed')->nullable();
            $table->text('technician_notes')->nullable();
            $table->text('parts_required')->nullable();
            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->decimal('actual_cost', 10, 2)->default(0);
            $table->boolean('customer_waiting')->default(false);
            $table->boolean('courtesy_car')->default(false);
            $table->string('vehicle_location')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['job_number', 'status']);
            $table->index('date_in');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_cards');
    }
};
