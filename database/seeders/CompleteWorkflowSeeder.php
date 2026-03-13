<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Appointment;
use App\Models\JobCard;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CompleteWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating complete workflow test data...');

        // Get existing customers
        $customers = Customer::all();
        
        if ($customers->count() < 3) {
            $this->command->error('Please ensure at least 3 customers exist before running this seeder');
            return;
        }

        // Get technician
        $technician = User::where('role', 'technician')->first();
        
        if (!$technician) {
            $this->command->error('No technician found. Please run StaffSeeder first.');
            return;
        }

        // Create vehicles for customers
        $vehicle1 = Vehicle::create([
            'customer_id' => $customers[0]->id,
            'registration_number' => 'AB12 CDE',
            'make' => 'Ford',
            'model' => 'Focus',
            'year' => 2020,
            'vin' => 'WF0AXXGCDA1234567',
            'color' => 'Blue',
            'fuel_type' => 'Petrol',
            'engine_size' => 1500,
            'mileage' => 25000,
        ]);

        $vehicle2 = Vehicle::create([
            'customer_id' => $customers[1]->id,
            'registration_number' => 'XY34 FGH',
            'make' => 'Volkswagen',
            'model' => 'Golf',
            'year' => 2019,
            'vin' => 'WVWZZZ1KZBW123456',
            'color' => 'Silver',
            'fuel_type' => 'Diesel',
            'engine_size' => 2000,
            'mileage' => 45000,
        ]);

        $vehicle3 = Vehicle::create([
            'customer_id' => $customers[2]->id,
            'registration_number' => 'LM56 NOP',
            'make' => 'BMW',
            'model' => '3 Series',
            'year' => 2021,
            'vin' => 'WBA8E5G50HNU12345',
            'color' => 'Black',
            'fuel_type' => 'Petrol',
            'engine_size' => 2000,
            'mileage' => 15000,
        ]);

        $this->command->info('✓ Created 3 vehicles');

        // Create appointments
        $appointment1 = Appointment::create([
            'customer_id' => $customers[0]->id,
            'vehicle_id' => $vehicle1->id,
            'scheduled_date' => Carbon::today()->addDays(2)->setTime(9, 0),
            'scheduled_end_date' => Carbon::today()->addDays(2)->setTime(11, 0),
            'duration_minutes' => 120,
            'appointment_type' => 'Full Service',
            'description' => 'Annual full service - oil change, filters, brake check',
            'status' => 'confirmed',
            'customer_notes' => 'Customer requested early morning slot',
        ]);

        $appointment2 = Appointment::create([
            'customer_id' => $customers[1]->id,
            'vehicle_id' => $vehicle2->id,
            'scheduled_date' => Carbon::today()->addDay()->setTime(14, 0),
            'scheduled_end_date' => Carbon::today()->addDay()->setTime(15, 0),
            'duration_minutes' => 60,
            'appointment_type' => 'MOT Test',
            'description' => 'MOT renewal - expires this week',
            'status' => 'confirmed',
            'customer_notes' => 'MOT due date: ' . Carbon::today()->addDays(3)->format('d/m/Y'),
        ]);

        $appointment3 = Appointment::create([
            'customer_id' => $customers[2]->id,
            'vehicle_id' => $vehicle3->id,
            'scheduled_date' => Carbon::today()->setTime(10, 30),
            'scheduled_end_date' => Carbon::today()->setTime(12, 30),
            'duration_minutes' => 120,
            'appointment_type' => 'Diagnostics',
            'description' => 'Engine warning light - diagnostic scan required',
            'status' => 'in_progress',
            'customer_notes' => 'Customer reports intermittent engine warning light',
        ]);

        $this->command->info('✓ Created 3 appointments');

        // Create job cards
        $jobCard1 = JobCard::create([
            'customer_id' => $customers[2]->id,
            'vehicle_id' => $vehicle3->id,
            'appointment_id' => $appointment3->id,
            'job_number' => 'JOB-' . str_pad(1, 6, '0', STR_PAD_LEFT),
            'description' => 'Diagnostic scan for engine warning light',
            'status' => 'in_progress',
            'priority' => 'high',
            'assigned_to' => $technician->id,
            'assigned_at' => Carbon::now(),
            'estimated_completion' => Carbon::today()->setTime(12, 0),
            'labour_hours' => 1.5,
            'labour_rate' => 60.00,
            'parts_cost' => 0.00,
            'labour_cost' => 90.00,
            'total_cost' => 90.00,
            'vat_amount' => 18.00,
            'total_price' => 108.00,
        ]);

        $jobCard2 = JobCard::create([
            'customer_id' => $customers[0]->id,
            'vehicle_id' => $vehicle1->id,
            'job_number' => 'JOB-' . str_pad(2, 6, '0', STR_PAD_LEFT),
            'description' => 'Brake pad replacement - front axle',
            'status' => 'completed',
            'priority' => 'medium',
            'assigned_to' => $technician->id,
            'assigned_at' => Carbon::yesterday(),
            'completed_at' => Carbon::today()->setTime(11, 30),
            'labour_hours' => 2.0,
            'labour_rate' => 60.00,
            'parts_cost' => 85.00,
            'labour_cost' => 120.00,
            'total_cost' => 205.00,
            'vat_amount' => 41.00,
            'total_price' => 246.00,
        ]);

        $this->command->info('✓ Created 2 job cards');

        // Create invoices
        $invoice1 = Invoice::create([
            'customer_id' => $customers[0]->id,
            'vehicle_id' => $vehicle1->id,
            'job_card_id' => $jobCard2->id,
            'invoice_number' => 'INV-' . str_pad(1, 6, '0', STR_PAD_LEFT),
            'date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(30),
            'subtotal' => 205.00,
            'vat' => 41.00,
            'total' => 246.00,
            'status' => 'pending',
            'notes' => 'Payment due within 30 days',
        ]);

        $invoice2 = Invoice::create([
            'customer_id' => $customers[1]->id,
            'vehicle_id' => $vehicle2->id,
            'invoice_number' => 'INV-' . str_pad(2, 6, '0', STR_PAD_LEFT),
            'date' => Carbon::yesterday(),
            'due_date' => Carbon::today()->addDays(14),
            'subtotal' => 150.00,
            'vat' => 30.00,
            'total' => 180.00,
            'status' => 'paid',
            'payment_method' => 'card',
            'payment_date' => Carbon::today(),
            'notes' => 'Paid via debit card',
        ]);

        $this->command->info('✓ Created 2 invoices');

        // Summary
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('Complete Workflow Test Data Created Successfully!');
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('Vehicles: 3 (AB12 CDE, XY34 FGH, LM56 NOP)');
        $this->command->info('Appointments: 3 (1 today, 1 tomorrow, 1 day after)');
        $this->command->info('Job Cards: 2 (1 in-progress, 1 completed)');
        $this->command->info('Invoices: 2 (1 pending, 1 paid)');
        $this->command->info('═══════════════════════════════════════════════════');
    }
}
