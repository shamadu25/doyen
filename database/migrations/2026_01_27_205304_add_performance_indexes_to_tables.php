<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper to safely add index
        $addIndex = function($table, $columns, $indexName) {
            $columnList = is_array($columns) ? implode('`, `', $columns) : $columns;
            try {
                DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$columnList}`)");
            } catch (\Exception $e) {
                // Index already exists, skip
            }
        };

        // Customers table indexes
        $addIndex('customers', 'phone', 'customers_phone_index');
        $addIndex('customers', ['customer_type', 'created_at'], 'customers_type_created_index');
        $addIndex('customers', 'created_at', 'customers_created_at_index');

        // Vehicles table indexes
        $addIndex('vehicles', 'registration_number', 'vehicles_registration_index');
        $addIndex('vehicles', ['make', 'model'], 'vehicles_make_model_index');
        $addIndex('vehicles', 'mot_due_date', 'vehicles_mot_due_index');

        // Appointments table indexes
        $addIndex('appointments', 'scheduled_date', 'appointments_scheduled_date_index');
        $addIndex('appointments', ['customer_id', 'status'], 'appointments_customer_status_index');
        $addIndex('appointments', 'status', 'appointments_status_index');
        $addIndex('appointments', 'created_at', 'appointments_created_at_index');

        // Job Cards table indexes
        $addIndex('job_cards', 'job_number', 'job_cards_number_index');
        $addIndex('job_cards', ['customer_id', 'status'], 'job_cards_customer_status_index');
        $addIndex('job_cards', 'status', 'job_cards_status_index');
        $addIndex('job_cards', 'created_at', 'job_cards_created_at_index');

        // Invoices table indexes
        $addIndex('invoices', 'invoice_number', 'invoices_number_index');
        $addIndex('invoices', ['customer_id', 'status'], 'invoices_customer_status_index');
        $addIndex('invoices', 'status', 'invoices_status_index');
        $addIndex('invoices', 'invoice_date', 'invoices_date_index');
        $addIndex('invoices', 'due_date', 'invoices_due_date_index');

        // Parts table indexes
        $addIndex('parts', 'part_number', 'parts_part_number_index');
        $addIndex('parts', ['category', 'is_active'], 'parts_category_active_index');
        $addIndex('parts', 'manufacturer', 'parts_manufacturer_index');
        $addIndex('parts', ['stock_quantity', 'minimum_stock'], 'parts_stock_level_index');

        // Services table indexes
        $addIndex('services', 'code', 'services_code_index');
        $addIndex('services', ['category', 'is_active'], 'services_category_active_index');

        // Payments table indexes
        $addIndex('payments', ['payment_date', 'payment_method'], 'payments_date_method_index');
        $addIndex('payments', 'payment_method', 'payments_method_index');

        // Vehicle Health Checks table indexes
        $addIndex('vehicle_health_checks', 'status', 'health_checks_status_index');
        $addIndex('vehicle_health_checks', 'created_at', 'health_checks_created_at_index');

        // MOT Tests table indexes
        $addIndex('mot_tests', 'test_date', 'mot_tests_date_index');
        $addIndex('mot_tests', 'expiry_date', 'mot_tests_expiry_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Helper to safely drop index
        $dropIndex = function($table, $indexName) {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
            } catch (\Exception $e) {
                // Index doesn't exist, skip
            }
        };

        // Drop indexes in reverse order
        $dropIndex('mot_tests', 'mot_tests_expiry_index');
        $dropIndex('mot_tests', 'mot_tests_date_index');

        $dropIndex('vehicle_health_checks', 'health_checks_created_at_index');
        $dropIndex('vehicle_health_checks', 'health_checks_status_index');

        $dropIndex('payments', 'payments_method_index');
        $dropIndex('payments', 'payments_date_method_index');

        $dropIndex('services', 'services_category_active_index');
        $dropIndex('services', 'services_code_index');

        $dropIndex('parts', 'parts_stock_level_index');
        $dropIndex('parts', 'parts_manufacturer_index');
        $dropIndex('parts', 'parts_category_active_index');
        $dropIndex('parts', 'parts_part_number_index');

        $dropIndex('invoices', 'invoices_due_date_index');
        $dropIndex('invoices', 'invoices_date_index');
        $dropIndex('invoices', 'invoices_status_index');
        $dropIndex('invoices', 'invoices_customer_status_index');
        $dropIndex('invoices', 'invoices_number_index');

        $dropIndex('job_cards', 'job_cards_created_at_index');
        $dropIndex('job_cards', 'job_cards_status_index');
        $dropIndex('job_cards', 'job_cards_customer_status_index');
        $dropIndex('job_cards', 'job_cards_number_index');

        $dropIndex('appointments', 'appointments_created_at_index');
        $dropIndex('appointments', 'appointments_status_index');
        $dropIndex('appointments', 'appointments_customer_status_index');
        $dropIndex('appointments', 'appointments_scheduled_date_index');

        $dropIndex('vehicles', 'vehicles_mot_due_index');
        $dropIndex('vehicles', 'vehicles_make_model_index');
        $dropIndex('vehicles', 'vehicles_registration_index');

        $dropIndex('customers', 'customers_created_at_index');
        $dropIndex('customers', 'customers_type_created_index');
        $dropIndex('customers', 'customers_phone_index');
    }
};
