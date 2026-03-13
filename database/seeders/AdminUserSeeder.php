<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@doyenauto.co.uk')->first();

        if ($user) {
            $user->update([
                'name'               => 'Ganiyu Ajayi',
                'password'           => Hash::make('DoyenAdmin2026'),
                'role'               => 'admin',
                'is_active'          => true,
                'email_verified_at'  => now(),
            ]);
            $this->command->info('Admin user updated: admin@doyenauto.co.uk');
        } else {
            User::create([
                'name'               => 'Ganiyu Ajayi',
                'email'              => 'admin@doyenauto.co.uk',
                'password'           => Hash::make('DoyenAdmin2026'),
                'role'               => 'admin',
                'is_active'          => true,
                'email_verified_at'  => now(),
            ]);
            $this->command->info('Admin user created: admin@doyenauto.co.uk');
        }

        $this->command->info('Password: DoyenAdmin2026');
    }
}
