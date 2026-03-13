<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mot_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_card_id')->nullable()->constrained()->onDelete('set null');
            $table->string('test_number')->unique()->nullable();
            $table->dateTime('test_date');
            $table->date('expiry_date')->nullable();
            $table->integer('mileage')->nullable();
            $table->string('test_result')->default('booked'); // pass, fail, prs (pass with rectification)
            $table->string('test_class')->default('4'); // 1, 2, 3, 4, 5, 7
            $table->json('advisories')->nullable();
            $table->json('failures')->nullable();
            $table->json('dvsa_data')->nullable(); // DVSA API data
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('test_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mot_tests');
    }
};
