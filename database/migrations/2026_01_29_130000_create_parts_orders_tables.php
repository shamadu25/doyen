<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('job_card_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who placed the order
            $table->string('supplier')->default('eurocarparts'); // eurocarparts, gsf, autodoc, etc.
            $table->string('supplier_order_reference')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('vat', 10, 2)->default(0);
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('delivery_method')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['supplier', 'status']);
            $table->index('job_card_id');
            $table->index('expected_delivery_date');
        });

        Schema::create('parts_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parts_order_id')->constrained()->onDelete('cascade');
            $table->string('part_number');
            $table->string('supplier_part_number')->nullable();
            $table->string('part_name');
            $table->text('description')->nullable();
            $table->string('manufacturer')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();

            $table->index('parts_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts_order_items');
        Schema::dropIfExists('parts_orders');
    }
};
