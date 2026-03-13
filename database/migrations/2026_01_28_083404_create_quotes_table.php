<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            $table->date('quote_date');
            $table->date('valid_until');
            $table->integer('validity_days')->default(30);
            $table->enum('status', ['draft', 'sent', 'approved', 'declined', 'expired', 'converted'])->default('draft');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(20.00);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_to_job_card_id')->nullable()->constrained('job_cards')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->onDelete('cascade');
            $table->enum('item_type', ['service', 'part', 'labour']);
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('part_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
    }
};
