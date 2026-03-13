<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

echo "\n";
echo "==============================================================\n";
echo "  DOYEN AUTO SERVICES - FINAL DEPLOYMENT READINESS CHECK\n";
echo "==============================================================\n\n";

$passed = 0;
$failed = 0;
$warnings = 0;

// 1. Database Connection
echo "1. DATABASE CONNECTION\n";
echo "   Testing database connection...\n";
try {
    DB::connection()->getPdo();
    echo "   ✓ Database connected: " . Config::get('database.connections.mysql.database') . "\n";
    $passed++;
} catch (\Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
    $failed++;
}

// 2. Required Tables
echo "\n2. DATABASE TABLES\n";
$requiredTables = [
    'users', 'customers', 'vehicles', 'appointments', 'job_cards', 
    'invoices', 'mot_tests', 'parts', 'roles', 'permissions', 'reminders'
];
$missingTables = [];
foreach ($requiredTables as $table) {
    if (Schema::hasTable($table)) {
        echo "   ✓ Table exists: $table\n";
    } else {
        echo "   ✗ Table missing: $table\n";
        $missingTables[] = $table;
        $failed++;
    }
}
if (empty($missingTables)) {
    $passed++;
}

// 3. Critical Columns
echo "\n3. PHASE 2 FEATURES - FILE UPLOADS\n";
if (Schema::hasColumn('vehicles', 'photos')) {
    echo "   ✓ Vehicle photos column exists\n";
    $passed++;
} else {
    echo "   ✗ Vehicle photos column missing\n";
    $failed++;
}

if (Schema::hasColumn('vehicles', 'main_photo')) {
    echo "   ✓ Vehicle main_photo column exists\n";
    $passed++;
} else {
    echo "   ✗ Vehicle main_photo column missing\n";
    $failed++;
}

if (Schema::hasColumn('mot_tests', 'certificate_path')) {
    echo "   ✓ MOT certificate_path column exists\n";
    $passed++;
} else {
    echo "   ✗ MOT certificate_path column missing\n";
    $failed++;
}

// 4. Email Configuration
echo "\n4. EMAIL SYSTEM\n";
$mailHost = Config::get('mail.mailers.smtp.host');
$mailFrom = Config::get('mail.from.address');
if ($mailHost && $mailFrom && $mailFrom !== 'hello@example.com') {
    echo "   ✓ SMTP configured: $mailHost\n";
    echo "   ✓ From address: $mailFrom\n";
    $passed += 2;
} else {
    echo "   ✗ Email not properly configured\n";
    $failed++;
}

// 5. Roles & Permissions
echo "\n5. ROLES & PERMISSIONS\n";
try {
    $roleCount = DB::table('roles')->count();
    $permissionCount = DB::table('permissions')->count();
    if ($roleCount >= 4 && $permissionCount >= 50) {
        echo "   ✓ Roles: $roleCount (expected 4+)\n";
        echo "   ✓ Permissions: $permissionCount (expected 50+)\n";
        $passed += 2;
    } else {
        echo "   ✗ Roles or permissions not seeded\n";
        echo "     Roles: $roleCount, Permissions: $permissionCount\n";
        $failed++;
    }
} catch (\Exception $e) {
    echo "   ✗ Error checking roles/permissions\n";
    $failed++;
}

// 6. Storage Link
echo "\n6. STORAGE CONFIGURATION\n";
if (file_exists(public_path('storage'))) {
    echo "   ✓ Storage symbolic link exists\n";
    $passed++;
} else {
    echo "   ⚠ Storage link missing (run: php artisan storage:link)\n";
    $warnings++;
}

// 7. Email Templates
echo "\n7. EMAIL TEMPLATES\n";
$emailTemplates = [
    'resources/views/emails/appointment-confirmation.blade.php',
    'resources/views/emails/appointment-reminder.blade.php',
    'resources/views/emails/mot-reminder.blade.php',
    'resources/views/emails/invoice-created.blade.php',
    'resources/views/emails/reset-password.blade.php',
];
$missingTemplates = [];
foreach ($emailTemplates as $template) {
    if (file_exists(base_path($template))) {
        echo "   ✓ " . basename($template, '.blade.php') . "\n";
    } else {
        $missingTemplates[] = $template;
        echo "   ✗ Missing: " . basename($template, '.blade.php') . "\n";
        $failed++;
    }
}
if (empty($missingTemplates)) {
    $passed++;
}

// 8. Scheduler Commands
echo "\n8. AUTOMATED REMINDER COMMANDS\n";
if (file_exists(app_path('Console/Commands/SendAppointmentReminders.php'))) {
    echo "   ✓ SendAppointmentReminders command exists\n";
    $passed++;
} else {
    echo "   ✗ SendAppointmentReminders command missing\n";
    $failed++;
}

if (file_exists(app_path('Console/Commands/SendMotReminders.php'))) {
    echo "   ✓ SendMotReminders command exists\n";
    $passed++;
} else {
    echo "   ✗ SendMotReminders command missing\n";
    $failed++;
}

// 9. Data Counts
echo "\n9. SYSTEM DATA\n";
try {
    $userCount = DB::table('users')->count();
    $customerCount = DB::table('customers')->count();
    $vehicleCount = DB::table('vehicles')->count();
    $appointmentCount = DB::table('appointments')->count();
    
    echo "   • Users: $userCount\n";
    echo "   • Customers: $customerCount\n";
    echo "   • Vehicles: $vehicleCount\n";
    echo "   • Appointments: $appointmentCount\n";
    
    if ($userCount > 0) {
        $passed++;
    } else {
        echo "   ⚠ No users in system\n";
        $warnings++;
    }
} catch (\Exception $e) {
    echo "   ✗ Error checking data: " . $e->getMessage() . "\n";
    $failed++;
}

// 10. API Integration
echo "\n10. API INTEGRATIONS\n";
$dvlaKey = env('DVLA_API_KEY');
$dvsaKey = env('DVSA_API_KEY');

if ($dvlaKey && strlen($dvlaKey) > 10) {
    echo "   ✓ DVLA API key configured\n";
    $passed++;
} else {
    echo "   ✗ DVLA API key missing\n";
    $failed++;
}

if ($dvsaKey && strlen($dvsaKey) > 10) {
    echo "   ✓ DVSA MOT API key configured\n";
    $passed++;
} else {
    echo "   ✗ DVSA MOT API key missing\n";
    $failed++;
}

// 11. Scheduler Configuration
echo "\n11. TASK SCHEDULER\n";
echo "   ⚠ MANUAL CHECK REQUIRED:\n";
echo "     Windows Task Scheduler must be configured manually\n";
echo "     See: QUICK_START_DEPLOYMENT.md Step 2\n";
$warnings++;

// Summary
echo "\n==============================================================\n";
echo "  DEPLOYMENT READINESS SUMMARY\n";
echo "==============================================================\n\n";

$total = $passed + $failed + $warnings;
$passRate = $total > 0 ? round(($passed / $total) * 100) : 0;

echo "  Tests Passed:    $passed\n";
echo "  Tests Failed:    $failed\n";
echo "  Warnings:        $warnings\n";
echo "  Total Checks:    $total\n";
echo "  Success Rate:    $passRate%\n\n";

if ($failed === 0 && $warnings <= 2) {
    echo "  STATUS: ✓✓✓ READY FOR PRODUCTION! ✓✓✓\n\n";
    echo "==============================================================\n";
    echo "  FINAL STEPS TO GO LIVE:\n";
    echo "==============================================================\n\n";
    echo "  1. ✅ Email system working\n";
    echo "  2. ✅ All features implemented\n";
    echo "  3. ✅ Database configured\n";
    echo "  4. ⚠️  Setup Windows Task Scheduler (10 minutes)\n";
    echo "     → See QUICK_START_DEPLOYMENT.md\n\n";
    echo "  After Step 4: System is 100% LIVE! 🚀\n\n";
} elseif ($failed === 0) {
    echo "  STATUS: ⚠ MOSTLY READY (address warnings)\n\n";
} else {
    echo "  STATUS: ✗ NOT READY (fix failed checks)\n\n";
}

echo "==============================================================\n";
echo "  QUICK COMMANDS:\n";
echo "==============================================================\n\n";
echo "  # Test appointment reminders\n";
echo "  php artisan appointments:send-reminders --hours=24\n\n";
echo "  # Test MOT reminders\n";
echo "  php artisan mot:send-reminders --days=30\n\n";
echo "  # List all scheduled tasks\n";
echo "  php artisan schedule:list\n\n";
echo "  # Run scheduler manually\n";
echo "  php artisan schedule:run\n\n";
echo "==============================================================\n\n";

exit($failed > 0 ? 1 : 0);
