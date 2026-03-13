<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_card_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('part_id')->nullable()->constrained()->onDelete('set null');
            $table->string('part_number')->nullable();
            $table->string('part_name');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(20.00);
            $table->string('status')->default('pending'); // pending, ordered, received, fitted
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_card_parts');
    }
};
