<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecu_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();

            // Relations
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_card_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();

            // Job classification
            $table->enum('category', [
                'diagnostics',
                'remapping',
                'airbag_srs',
                'emissions',
                'immobiliser',
                'mileage_correction',
                'electrical',
                'other',
            ])->default('diagnostics');
            $table->string('service_type'); // e.g. ecu-remapping, airbag-crash-data-reset
            $table->string('service_label')->nullable(); // human-friendly label

            // Status & dates
            $table->enum('status', ['booked', 'in_progress', 'completed', 'on_hold', 'cancelled'])->default('booked');
            $table->date('date_in');
            $table->date('date_completed')->nullable();
            $table->integer('mileage')->nullable();

            // ECU / module info
            $table->string('ecu_part_number')->nullable();
            $table->string('ecu_software_version')->nullable();
            $table->string('ecu_hardware_version')->nullable();
            $table->string('immo_ref')->nullable(); // immobiliser/key reference

            // Fault codes
            $table->json('fault_codes_found')->nullable();    // array of DTCs
            $table->json('fault_codes_cleared')->nullable();  // array of DTCs cleared
            $table->boolean('all_codes_cleared')->default(false);

            // Work description
            $table->text('work_required')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('pre_condition')->nullable();
            $table->text('post_condition')->nullable();
            $table->text('internal_notes')->nullable();

            // Category-specific details (flexible JSON)
            // Remapping: bhp_before, bhp_after, torque_before, torque_after, stage, tune_file_ref, fuel_type, is_dyno_tested
            // Airbag:    modules_reset[], crash_data_cleared, srs_light_cleared, seatbelts_reset
            // Emissions: dpf_before, dpf_after, egr_solution, adblue_solution
            // Mileage:   mileage_before, mileage_after, cluster_replaced
            $table->json('details')->nullable();

            // Outcome
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_invoiced')->default(false);
            $table->integer('warranty_months')->default(0);
            $table->boolean('customer_notified')->default(false);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecu_jobs');
    }
};
