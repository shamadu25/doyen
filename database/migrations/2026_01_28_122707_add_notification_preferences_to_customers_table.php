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
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('email_notifications')->default(true)->after('is_active');
            $table->boolean('sms_notifications')->default(false)->after('email_notifications');
            $table->boolean('appointment_reminders')->default(true)->after('sms_notifications');
            $table->boolean('service_reminders')->default(true)->after('appointment_reminders');
            $table->boolean('mot_reminders')->default(true)->after('service_reminders');
            $table->boolean('marketing_emails')->default(false)->after('mot_reminders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'email_notifications',
                'sms_notifications',
                'appointment_reminders',
                'service_reminders',
                'mot_reminders',
                'marketing_emails',
            ]);
        });
    }
};
