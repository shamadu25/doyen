<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->timestamps();
                $table->index('group');
            });
        }

        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->text('description');
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->json('properties')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
                $table->index(['subject_type', 'subject_id']);
                $table->index('action');
            });
        }

        if (!Schema::hasTable('reminders')) {
            Schema::create('reminders', function (Blueprint $table) {
                $table->id();
                $table->string('type'); // mot_expiry, service_due, booking_reminder
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
                $table->date('due_date');
                $table->string('status')->default('pending');
                $table->datetime('sent_at')->nullable();
                $table->text('message')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['status', 'due_date']);
                $table->index('type');
            });
        }

        if (!Schema::hasTable('inventory_transactions')) {
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('part_id')->constrained()->cascadeOnDelete();
                $table->string('type'); // in, out, adjustment
                $table->integer('quantity');
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->decimal('cost_price', 10, 2)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
                $table->index(['reference_type', 'reference_id']);
                $table->index('type');
            });
        }

        // Add missing columns to mot_tests if needed
        if (Schema::hasTable('mot_tests') && !Schema::hasColumn('mot_tests', 'certificate_path')) {
            Schema::table('mot_tests', function (Blueprint $table) {
                $table->string('certificate_path')->nullable()->after('notes');
                $table->string('tester_name')->nullable()->after('certificate_path');
                $table->string('status')->default('booked')->after('test_result');
            });
        }

        // Add outstanding_balance to customers if missing
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'outstanding_balance')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->decimal('outstanding_balance', 10, 2)->default(0)->after('notes');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
    }
};
