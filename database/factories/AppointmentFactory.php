<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'customer_id'      => Customer::factory(),
            'vehicle_id'       => Vehicle::factory(),
            'appointment_type' => fake()->randomElement(['mot', 'service', 'repair', 'diagnosis']),
            'scheduled_date'   => fake()->dateTimeBetween('+1 day', '+30 days'),
            'duration_minutes' => fake()->randomElement([60, 90, 120]),
            'status'           => 'pending',
            'description'      => 'Test appointment',
        ];
    }
}
