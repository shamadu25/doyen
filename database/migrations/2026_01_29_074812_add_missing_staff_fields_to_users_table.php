<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add missing staff fields
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'position')) {
                $table->string('position')->nullable()->after('department');
            }
            if (!Schema::hasColumn('users', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->default(0)->after('hourly_rate');
            }
            if (!Schema::hasColumn('users', 'skills')) {
                $table->json('skills')->nullable()->after('commission_rate');
            }
            if (!Schema::hasColumn('users', 'certifications')) {
                $table->json('certifications')->nullable()->after('skills');
            }
            if (!Schema::hasColumn('users', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('certifications');
            }
            if (!Schema::hasColumn('users', 'notes')) {
                $table->text('notes')->nullable()->after('hire_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'department',
                'position',
                'commission_rate',
                'skills',
                'certifications',
                'hire_date',
                'notes'
            ]);
        });
    }
};
