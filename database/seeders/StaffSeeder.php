<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Senior Technician
        User::create([
            'name' => 'John Smith',
            'email' => 'john.technician@doyenauto.co.uk',
            'password' => Hash::make('password123'),
            'employee_id' => 'TECH001',
            'role' => 'technician',
            'phone' => '07700 900001',
            'department' => 'Service',
            'position' => 'Senior Technician',
            'hourly_rate' => 18.50,
            'commission_rate' => 10.00,
            'skills' => ['MOT Testing', 'Diagnostics', 'Engine Repair', 'Brake Systems'],
            'certifications' => ['DVSA MOT Tester', 'City & Guilds Level 3', 'IMI Accredited'],
            'hire_date' => now()->subYears(5),
            'is_active' => true,
        ]);

        // Create Junior Technician
        User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah.technician@doyenauto.co.uk',
            'password' => Hash::make('password123'),
            'employee_id' => 'TECH002',
            'role' => 'technician',
            'phone' => '07700 900002',
            'department' => 'MOT',
            'position' => 'MOT Tester',
            'hourly_rate' => 15.00,
            'commission_rate' => 8.00,
            'skills' => ['MOT Testing', 'Brake Systems'],
            'certifications' => ['DVSA MOT Tester'],
            'hire_date' => now()->subYears(2),
            'is_active' => true,
        ]);

        // Create Manager
        User::create([
            'name' => 'David Williams',
            'email' => 'david.manager@doyenauto.co.uk',
            'password' => Hash::make('password123'),
            'employee_id' => 'MGR001',
            'role' => 'manager',
            'phone' => '07700 900003',
            'department' => 'Management',
            'position' => 'Workshop Manager',
            'hourly_rate' => 22.00,
            'commission_rate' => 5.00,
            'skills' => [],
            'certifications' => ['IMI Accredited'],
            'hire_date' => now()->subYears(8),
            'is_active' => true,
        ]);

        // Create Receptionist
        User::create([
            'name' => 'Emma Brown',
            'email' => 'emma.reception@doyenauto.co.uk',
            'password' => Hash::make('password123'),
            'employee_id' => 'REC001',
            'role' => 'receptionist',
            'phone' => '07700 900004',
            'department' => 'Reception',
            'position' => 'Front Desk',
            'hourly_rate' => 12.00,
            'commission_rate' => 0.00,
            'skills' => [],
            'certifications' => [],
            'hire_date' => now()->subYear(),
            'is_active' => true,
        ]);

        $this->command->info('Staff members created successfully!');
    }
}
