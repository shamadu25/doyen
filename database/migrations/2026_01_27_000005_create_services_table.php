<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('category'); // mot, service, repair, diagnosis, bodywork
            $table->decimal('default_price', 10, 2);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->integer('estimated_duration_minutes')->default(60);
            $table->decimal('vat_rate', 5, 2)->default(20.00);
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_booking')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
