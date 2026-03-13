<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_card_id')->nullable()->constrained()->onDelete('set null');
            $table->date('service_date');
            $table->integer('mileage');
            $table->string('service_type'); // interim, full, major
            $table->text('work_carried_out')->nullable();
            $table->date('next_service_date')->nullable();
            $table->integer('next_service_mileage')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_services');
    }
};
