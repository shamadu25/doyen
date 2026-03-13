<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'customer_id'         => Customer::factory(),
            'registration_number' => strtoupper(fake()->bothify('??##???')),
            'make'                => fake()->randomElement(['FORD', 'VAUXHALL', 'BMW', 'AUDI', 'TOYOTA']),
            'model'               => fake()->randomElement(['FOCUS', 'ASTRA', '320D', 'A4', 'YARIS']),
            'year'                => fake()->numberBetween(2010, 2024),
            'color'               => fake()->colorName(),
            'fuel_type'           => fake()->randomElement(['Petrol', 'Diesel']),
            'engine_size'         => fake()->randomElement([1200, 1400, 1600, 2000]),
            'transmission'        => fake()->randomElement(['Manual', 'Automatic']),
            'mileage'             => fake()->numberBetween(5000, 100000),
            'is_active'           => true,
        ];
    }
}
