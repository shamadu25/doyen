<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Appointment;
use App\Models\JobCard;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompleteSystemTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating comprehensive test data...');

        // 1. Create Test Customers
        $this->command->info('👥 Creating customers...');
        
        $customer1 = Customer::create([
            'first_name' => 'James',
            'last_name' => 'Anderson',
            'email' => 'james.anderson@example.com',
            'phone' => '07700 900100',
            'address' => '123 High Street',
            'city' => 'London',
            'postcode' => 'SW1A 1AA',
            'password' => Hash::make('customer123'),
            'marketing_emails' => true,
            'sms_notifications' => true,
            'email_notifications' => true,
        ]);

        $customer2 = Customer::create([
            'first_name' => 'Sophie',
            'last_name' => 'Williams',
            'email' => 'sophie.williams@example.com',
            'phone' => '07700 900101',
            'address' => '45 Oak Avenue',
            'city' => 'Manchester',
            'postcode' => 'M1 1AD',
            'password' => Hash::make('customer123'),
            'marketing_emails' => true,
            'sms_notifications' => true,
            'email_notifications' => true,
        ]);

        $customer3 = Customer::create([
            'first_name' => 'Michael',
            'last_name' => 'Brown',
            'email' => 'michael.brown@example.com',
            'phone' => '07700 900102',
            'address' => '78 Park Lane',
            'city' => 'Birmingham',
            'postcode' => 'B1 1AA',
            'password' => Hash::make('customer123'),
            'marketing_emails' => false,
            'sms_notifications' => true,
            'email_notifications' => true,
        ]);

        // 2. Create Test Vehicles
        $this->command->info('🚗 Creating vehicles...');

        $vehicle1 = Vehicle::create([
            'customer_id' => $customer1->id,
            'registration_number' => 'AB12 CDE',
            'make' => 'BMW',
            'model' => '3 Series',
            'year' => 2020,
            'color' => 'Black',
            'vin' => 'WBA8E9G51HNU12345',
            'engine_size' => 2000,
            'fuel_type' => 'Diesel',
            'transmission' => 'Automatic',
            'mileage' => 45000,
            'mot_due_date' => now()->addMonths(6),
            'service_due_date' => now()->addMonths(2),
            'notes' => 'Premium customer - white glove service',
        ]);

        $vehicle2 = Vehicle::create([
            'customer_id' => $customer1->id,
            'registration_number' => 'FG34 HIJ',
            'make' => 'Audi',
            'model' => 'A4',
            'year' => 2019,
            'color' => 'Silver',
            'vin' => 'WAUZZZ8V8JA123456',
            'engine_size' => 2000,
            'fuel_type' => 'Petrol',
            'transmission' => 'Manual',
            'mileage' => 52000,
            'mot_due_date' => now()->addMonths(8),
            'service_due_date' => now()->addMonth(),
        ]);

        $vehicle3 = Vehicle::create([
            'customer_id' => $customer2->id,
            'registration_number' => 'KL56 MNO',
            'make' => 'Ford',
            'model' => 'Focus',
            'year' => 2018,
            'color' => 'Blue',
            'vin' => 'WF0AXXGCDA123456',
            'engine_size' => 1600,
            'fuel_type' => 'Petrol',
            'transmission' => 'Manual',
            'mileage' => 68000,
            'mot_due_date' => now()->addMonth(),
            'service_due_date' => now()->addWeeks(3),
        ]);

        $vehicle4 = Vehicle::create([
            'customer_id' => $customer3->id,
            'registration_number' => 'PQ78 RST',
            'make' => 'Mercedes-Benz',
            'model' => 'C-Class',
            'year' => 2021,
            'color' => 'White',
            'vin' => 'WDD2050091F123456',
            'engine_size' => 2000,
            'fuel_type' => 'Diesel',
            'transmission' => 'Automatic',
            'mileage' => 28000,
            'mot_due_date' => now()->addYear(),
            'service_due_date' => now()->addMonths(4),
        ]);

        // 3. Create Test Appointments
        $this->command->info('📅 Creating appointments...');

        $technician = User::where('role', 'technician')->first();

        Appointment::create([
            'customer_id' => $customer1->id,
            'vehicle_id' => $vehicle1->id,
            'appointment_date' => now()->addDays(2),
            'time_slot' => '09:00',
            'service_type' => 'Annual Service',
            'status' => 'confirmed',
            'notes' => 'Customer requested early morning appointment',
            'confirmed_at' => now(),
        ]);

        Appointment::create([
            'customer_id' => $customer2->id,
            'vehicle_id' => $vehicle3->id,
            'appointment_date' => now()->addWeeks(1),
            'time_slot' => '14:00',
            'service_type' => 'MOT Test',
            'status' => 'pending',
            'notes' => 'MOT due soon - customer called to book',
        ]);

        Appointment::create([
            'customer_id' => $customer3->id,
            'vehicle_id' => $vehicle4->id,
            'appointment_date' => now()->addDays(5),
            'time_slot' => '11:00',
            'service_type' => 'Diagnostics',
            'status' => 'confirmed',
            'notes' => 'Check engine light on - diagnostic required',
            'confirmed_at' => now(),
        ]);

        // 4. Create Completed Job Card with Invoice
        $this->command->info('🔧 Creating job cards and invoices...');

        $jobCard1 = JobCard::create([
            'job_number' => 'JOB-' . str_pad(1, 6, '0', STR_PAD_LEFT),
            'customer_id' => $customer1->id,
            'vehicle_id' => $vehicle2->id,
            'assigned_to' => $technician->id,
            'assigned_at' => now()->subDays(3),
            'status' => 'completed',
            'service_type' => 'Full Service',
            'description' => 'Full service including oil change, filters, brake check',
            'labour_hours' => 2.5,
            'labour_rate' => 65.00,
            'parts_cost' => 125.00,
            'total_price' => 287.50,
            'notes' => 'All filters replaced, brake pads at 60%',
            'started_at' => now()->subDays(3),
            'completed_at' => now()->subDays(2),
        ]);

        Invoice::create([
            'invoice_number' => 'INV-' . str_pad(1, 6, '0', STR_PAD_LEFT),
            'customer_id' => $customer1->id,
            'vehicle_id' => $vehicle2->id,
            'job_card_id' => $jobCard1->id,
            'issue_date' => now()->subDays(2),
            'due_date' => now()->addDays(28),
            'subtotal' => 239.58,
            'vat_amount' => 47.92,
            'discount_amount' => 0,
            'total_amount' => 287.50,
            'amount_paid' => 287.50,
            'status' => 'paid',
            'payment_method' => 'card',
            'paid_at' => now()->subDays(2),
            'notes' => 'Paid in full - thank you',
        ]);

        // 5. Create In-Progress Job Card
        $jobCard2 = JobCard::create([
            'job_number' => 'JOB-' . str_pad(2, 6, '0', STR_PAD_LEFT),
            'customer_id' => $customer2->id,
            'vehicle_id' => $vehicle3->id,
            'assigned_to' => $technician->id,
            'assigned_at' => now()->subHours(4),
            'status' => 'in_progress',
            'service_type' => 'Brake Service',
            'description' => 'Replace front brake pads and discs',
            'labour_hours' => 1.5,
            'labour_rate' => 65.00,
            'parts_cost' => 185.00,
            'total_price' => 282.50,
            'notes' => 'Customer reported squeaking noise',
            'started_at' => now()->subHours(3),
        ]);

        // 6. Create Pending Job Card
        JobCard::create([
            'job_number' => 'JOB-' . str_pad(3, 6, '0', STR_PAD_LEFT),
            'customer_id' => $customer3->id,
            'vehicle_id' => $vehicle4->id,
            'status' => 'pending',
            'service_type' => 'MOT & Service',
            'description' => 'MOT test and full service',
            'labour_hours' => 3.0,
            'labour_rate' => 65.00,
            'parts_cost' => 95.00,
            'total_price' => 290.00,
            'notes' => 'Awaiting customer approval for additional work',
        ]);

        $this->command->info('✅ Test data created successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   • 3 Customers created');
        $this->command->info('   • 4 Vehicles created');
        $this->command->info('   • 3 Appointments created');
        $this->command->info('   • 3 Job Cards created');
        $this->command->info('   • 1 Invoice created');
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('   Admin: admin@doyenauto.co.uk / password123');
        $this->command->info('   Customer 1: james.anderson@example.com / customer123');
        $this->command->info('   Customer 2: sophie.williams@example.com / customer123');
        $this->command->info('   Customer 3: michael.brown@example.com / customer123');
        $this->command->info('   Technician: john.technician@doyenauto.co.uk / password123');
    }
}
