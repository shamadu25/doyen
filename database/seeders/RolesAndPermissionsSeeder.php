<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Customer Management
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            
            // Vehicle Management
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',
            
            // Appointments
            'view appointments',
            'create appointments',
            'edit appointments',
            'delete appointments',
            'cancel appointments',
            
            // Job Cards
            'view job cards',
            'create job cards',
            'edit job cards',
            'delete job cards',
            'assign job cards',
            'complete job cards',
            
            // MOT Tests
            'view mot tests',
            'create mot tests',
            'edit mot tests',
            'delete mot tests',
            'perform mot tests',
            
            // Invoices
            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',
            'send invoices',
            'void invoices',
            
            // Payments
            'view payments',
            'create payments',
            'edit payments',
            'delete payments',
            'process refunds',
            
            // Parts & Inventory
            'view parts',
            'create parts',
            'edit parts',
            'delete parts',
            'manage stock',
            
            // Reports
            'view reports',
            'export reports',
            'view financial reports',
            
            // Users & Staff
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',
            
            // Settings
            'view settings',
            'edit settings',
            
            // Activity Logs
            'view activity logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // 1. Super Admin - All permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // 2. Manager - Most permissions except user management
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'view customers', 'create customers', 'edit customers',
            'view vehicles', 'create vehicles', 'edit vehicles',
            'view appointments', 'create appointments', 'edit appointments', 'cancel appointments',
            'view job cards', 'create job cards', 'edit job cards', 'assign job cards', 'complete job cards',
            'view mot tests', 'create mot tests', 'edit mot tests',
            'view invoices', 'create invoices', 'edit invoices', 'send invoices',
            'view payments', 'create payments',
            'view parts', 'create parts', 'edit parts', 'manage stock',
            'view reports', 'export reports', 'view financial reports',
            'view activity logs',
        ]);

        // 3. Technician - Job cards, MOT tests, limited access
        $technicianRole = Role::firstOrCreate(['name' => 'technician']);
        $technicianRole->givePermissionTo([
            'view customers',
            'view vehicles', 'edit vehicles', // Can update mileage
            'view appointments',
            'view job cards', 'edit job cards', 'complete job cards',
            'view mot tests', 'create mot tests', 'edit mot tests', 'perform mot tests',
            'view parts', 'manage stock', // Can use parts
            'view invoices', // Can see pricing
        ]);

        // 4. Receptionist - Front desk operations
        $receptionistRole = Role::firstOrCreate(['name' => 'receptionist']);
        $receptionistRole->givePermissionTo([
            'view customers', 'create customers', 'edit customers',
            'view vehicles', 'create vehicles', 'edit vehicles',
            'view appointments', 'create appointments', 'edit appointments', 'cancel appointments',
            'view job cards', 'create job cards',
            'view mot tests', 'create mot tests',
            'view invoices', 'send invoices',
            'view payments', 'create payments',
            'view parts',
        ]);

        // Assign roles to existing users if they don't have any
        $users = User::all();
        
        foreach ($users as $user) {
            if (!$user->hasAnyRole(['admin', 'manager', 'technician', 'receptionist'])) {
                // Check the role column (old system)
                if ($user->role) {
                    switch ($user->role) {
                        case 'admin':
                            $user->assignRole('admin');
                            break;
                        case 'manager':
                            $user->assignRole('manager');
                            break;
                        case 'technician':
                            $user->assignRole('technician');
                            break;
                        case 'receptionist':
                            $user->assignRole('receptionist');
                            break;
                        default:
                            // Default to receptionist for safety
                            $user->assignRole('receptionist');
                    }
                } else {
                    // If no role set, make first user admin, others receptionist
                    if ($user->id === 1) {
                        $user->assignRole('admin');
                    } else {
                        $user->assignRole('receptionist');
                    }
                }
            }
        }

        $this->command->info('✅ Roles and permissions created successfully!');
        $this->command->info('');
        $this->command->info('Roles created:');
        $this->command->info('  - Admin (all permissions)');
        $this->command->info('  - Manager (most operations)');
        $this->command->info('  - Technician (job cards & MOT)');
        $this->command->info('  - Receptionist (front desk)');
        $this->command->info('');
        $this->command->info('Total permissions: ' . Permission::count());
        $this->command->info('Existing users updated with roles');
    }
}
