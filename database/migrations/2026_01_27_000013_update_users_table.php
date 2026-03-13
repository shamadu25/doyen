<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('technician')->after('email'); // admin, manager, technician, receptionist
            $table->string('phone')->nullable()->after('role');
            $table->string('employee_id')->unique()->nullable()->after('phone');
            $table->decimal('hourly_rate', 10, 2)->default(0)->after('employee_id');
            $table->boolean('is_active')->default(true)->after('hourly_rate');
            $table->text('specializations')->nullable()->after('is_active');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'employee_id', 'hourly_rate', 'is_active', 'specializations']);
            $table->dropSoftDeletes();
        });
    }
};
