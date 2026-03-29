<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Part;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@doyenautos.co.uk'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Technician
        User::updateOrCreate(
            ['email' => 'tech@doyenautos.co.uk'],
            [
                'name' => 'John Technician',
                'password' => Hash::make('password'),
                'role' => 'technician',
            ]
        );

        // Default Settings
        $settings = [
            'garage_name' => 'Doyen Auto Services',
            'garage_address' => '59 Southcroft Rd, Rutherglen, Glasgow, G73 1UG',
            'garage_city' => 'Glasgow',
            'garage_postcode' => 'G73 1UG',
            'garage_phone' => '',
            'garage_email' => 'info@doyenautos.co.uk',
            'vat_rate' => '20',
            'vat_number' => '',
            'default_labour_rate' => '65.00',
            'booking_slot_duration' => '60',
            'invoice_prefix' => 'INV-',
            'invoice_terms' => 'Payment due within 30 days of invoice date.',
            'mot_station_number' => '',
            'sms_enabled' => '0',
            'sms_admin_phone' => '',
            'email_notifications' => '1',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        // Sample Customers
        $customers = [
            ['first_name' => 'James', 'last_name' => 'Wilson', 'email' => 'james.wilson@email.com', 'phone' => '07700900001', 'address' => '12 Main Street', 'city' => 'Glasgow', 'postcode' => 'G1 1AA'],
            ['first_name' => 'Sarah', 'last_name' => 'MacLeod', 'email' => 'sarah.macleod@email.com', 'phone' => '07700900002', 'address' => '45 High Road', 'city' => 'Rutherglen', 'postcode' => 'G73 2AB'],
            ['first_name' => 'David', 'last_name' => 'Campbell', 'email' => 'david.campbell@email.com', 'phone' => '07700900003', 'address' => '8 Station Lane', 'city' => 'Cambuslang', 'postcode' => 'G72 7AA'],
            ['first_name' => 'Emma', 'last_name' => 'Stewart', 'email' => 'emma.stewart@email.com', 'phone' => '07700900004', 'address' => '23 Park Avenue', 'city' => 'Glasgow', 'postcode' => 'G42 8BN'],
            ['first_name' => 'Robert', 'last_name' => 'Murray', 'email' => 'robert.murray@email.com', 'phone' => '07700900005', 'address' => '67 King Street', 'city' => 'Rutherglen', 'postcode' => 'G73 1DQ'],
        ];

        foreach ($customers as $data) {
            $customer = Customer::updateOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Each customer gets 1–2 vehicles
            if ($customer->wasRecentlyCreated) {
                $this->createVehiclesForCustomer($customer);
            }
        }

        // Sample Parts/Inventory
        $parts = [
            ['name' => 'Oil Filter', 'part_number' => 'OF-001', 'category' => 'Filters', 'cost_price' => 3.50, 'selling_price' => 8.99, 'stock_quantity' => 25, 'minimum_stock' => 10],
            ['name' => 'Air Filter', 'part_number' => 'AF-001', 'category' => 'Filters', 'cost_price' => 5.00, 'selling_price' => 14.99, 'stock_quantity' => 15, 'minimum_stock' => 5],
            ['name' => 'Brake Pads (Front)', 'part_number' => 'BP-001', 'category' => 'Brakes', 'cost_price' => 18.00, 'selling_price' => 39.99, 'stock_quantity' => 12, 'minimum_stock' => 4],
            ['name' => 'Brake Discs (Front Pair)', 'part_number' => 'BD-001', 'category' => 'Brakes', 'cost_price' => 35.00, 'selling_price' => 79.99, 'stock_quantity' => 6, 'minimum_stock' => 2],
            ['name' => '5W-30 Engine Oil (5L)', 'part_number' => 'EO-001', 'category' => 'Oil & Fluids', 'cost_price' => 18.00, 'selling_price' => 34.99, 'stock_quantity' => 20, 'minimum_stock' => 5],
            ['name' => 'Spark Plug (each)', 'part_number' => 'SP-001', 'category' => 'Ignition', 'cost_price' => 3.00, 'selling_price' => 7.99, 'stock_quantity' => 30, 'minimum_stock' => 10],
            ['name' => 'Wiper Blade (pair)', 'part_number' => 'WB-001', 'category' => 'Body', 'cost_price' => 6.00, 'selling_price' => 15.99, 'stock_quantity' => 10, 'minimum_stock' => 4],
            ['name' => 'Coolant 5L', 'part_number' => 'CL-001', 'category' => 'Oil & Fluids', 'cost_price' => 8.00, 'selling_price' => 18.99, 'stock_quantity' => 8, 'minimum_stock' => 3],
            ['name' => 'Brake Fluid DOT 4 (1L)', 'part_number' => 'BF-001', 'category' => 'Oil & Fluids', 'cost_price' => 5.00, 'selling_price' => 12.99, 'stock_quantity' => 10, 'minimum_stock' => 3],
            ['name' => 'Battery 063', 'part_number' => 'BT-063', 'category' => 'Electrical', 'cost_price' => 45.00, 'selling_price' => 89.99, 'stock_quantity' => 3, 'minimum_stock' => 2],
        ];

        foreach ($parts as $data) {
            Part::updateOrCreate(['part_number' => $data['part_number']], $data);
        }
    }

    private function createVehiclesForCustomer(Customer $customer): void
    {
        $vehicles = [
            ['registration_number' => 'SG23 ABC', 'make' => 'Ford', 'model' => 'Focus', 'year' => 2023, 'color' => 'Blue', 'fuel_type' => 'Petrol', 'engine_size' => '1.0'],
            ['registration_number' => 'SN21 DEF', 'make' => 'Vauxhall', 'model' => 'Corsa', 'year' => 2021, 'color' => 'White', 'fuel_type' => 'Petrol', 'engine_size' => '1.2'],
            ['registration_number' => 'SF20 GHI', 'make' => 'Volkswagen', 'model' => 'Golf', 'year' => 2020, 'color' => 'Grey', 'fuel_type' => 'Diesel', 'engine_size' => '2.0'],
            ['registration_number' => 'SA19 JKL', 'make' => 'BMW', 'model' => '3 Series', 'year' => 2019, 'color' => 'Black', 'fuel_type' => 'Diesel', 'engine_size' => '2.0'],
            ['registration_number' => 'SJ22 MNO', 'make' => 'Toyota', 'model' => 'Yaris', 'year' => 2022, 'color' => 'Red', 'fuel_type' => 'Hybrid', 'engine_size' => '1.5'],
            ['registration_number' => 'SK18 PQR', 'make' => 'Mercedes-Benz', 'model' => 'A-Class', 'year' => 2018, 'color' => 'Silver', 'fuel_type' => 'Petrol', 'engine_size' => '1.3'],
            ['registration_number' => 'SL20 STU', 'make' => 'Audi', 'model' => 'A3', 'year' => 2020, 'color' => 'White', 'fuel_type' => 'Petrol', 'engine_size' => '1.5'],
            ['registration_number' => 'SM17 VWX', 'make' => 'Nissan', 'model' => 'Qashqai', 'year' => 2017, 'color' => 'Brown', 'fuel_type' => 'Diesel', 'engine_size' => '1.5'],
        ];

        // Pick 1-2 random vehicles
        $selected = array_rand($vehicles, rand(1, 2));
        if (!is_array($selected)) $selected = [$selected];

        foreach ($selected as $idx) {
            // Ensure unique reg by appending customer id fragment
            $v = $vehicles[$idx];
            $v['registration_number'] = substr($v['registration_number'], 0, -1) . $customer->id;
            $v['customer_id'] = $customer->id;

            Vehicle::updateOrCreate(
                ['registration_number' => $v['registration_number']],
                $v
            );
        }
    }
}
