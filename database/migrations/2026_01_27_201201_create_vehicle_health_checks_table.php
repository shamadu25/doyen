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
        Schema::create('vehicle_health_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('inspector_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('inspection_area'); // e.g., "Front Tyres", "Brakes", "Engine"
            $table->enum('condition', ['green', 'amber', 'red']); // Traffic light system
            $table->text('notes')->nullable();
            $table->text('recommendation')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('requires_attention')->default(false);
            $table->boolean('customer_approved')->default(false);
            $table->timestamp('customer_approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_health_checks');
    }
};
