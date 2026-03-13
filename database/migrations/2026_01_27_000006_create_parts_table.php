<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('category')->nullable();
            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('vat_rate', 5, 2)->default(20.00);
            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->string('supplier')->nullable();
            $table->string('supplier_part_number')->nullable();
            $table->string('location')->nullable(); // shelf/bin location
            $table->json('tecdoc_data')->nullable(); // TecDoc integration data
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('part_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
