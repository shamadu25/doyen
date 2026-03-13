<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'first_name'   => fake()->firstName(),
            'last_name'    => fake()->lastName(),
            'email'        => fake()->unique()->safeEmail(),
            'phone'        => fake()->phoneNumber(),
            'address'      => fake()->streetAddress(),
            'city'         => 'Glasgow',
            'postcode'     => 'G1 1AA',
            'customer_type'=> 'individual',
            'is_active'    => true,
        ];
    }
}
