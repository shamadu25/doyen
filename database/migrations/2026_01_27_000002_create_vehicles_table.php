<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('registration_number')->unique();
            $table->string('vin')->nullable()->unique();
            $table->string('make');
            $table->string('model');
            $table->string('variant')->nullable();
            $table->string('color')->nullable();
            $table->integer('year');
            $table->string('fuel_type')->nullable();
            $table->string('transmission')->nullable();
            $table->integer('engine_size')->nullable();
            $table->integer('mileage')->default(0);
            $table->date('mot_due_date')->nullable();
            $table->date('tax_due_date')->nullable();
            $table->date('service_due_date')->nullable();
            $table->integer('service_due_mileage')->nullable();
            $table->json('dvla_data')->nullable(); // Store DVLA API data
            $table->json('tecdoc_data')->nullable(); // Store TecDoc data
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
