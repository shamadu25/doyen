<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_card_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['service', 'parts', 'labor', 'bonus', 'manual']);
            $table->decimal('base_amount', 10, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('paid_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('period_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
