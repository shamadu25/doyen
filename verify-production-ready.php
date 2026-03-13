#!/usr/bin/env php
<?php
/**
 * Production Readiness Verification Script
 * Checks all 5 critical fixes are in place
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n🔍 DOYEN AUTO - Production Readiness Check\n";
echo str_repeat("=", 50) . "\n\n";

$passed = 0;
$failed = 0;

// Check 1: Environment Security
echo "1️⃣  Checking Environment Security...\n";
if (config('app.debug') === false) {
    echo "   ✅ APP_DEBUG is false\n";
    $passed++;
} else {
    echo "   ❌ APP_DEBUG is still true - SECURITY RISK!\n";
    $failed++;
}

if (config('session.encrypt') === true) {
    echo "   ✅ Session encryption enabled\n";
    $passed++;
} else {
    echo "   ❌ Session encryption disabled\n";
    $failed++;
}

echo "\n";

// Check 2: Rate Limiting
echo "2️⃣  Checking Rate Limiting...\n";
$routeCollection = app('router')->getRoutes();
$throttledRoutes = 0;
foreach ($routeCollection as $route) {
    $middleware = $route->gatherMiddleware();
    foreach ($middleware as $mw) {
        if (strpos($mw, 'throttle') !== false) {
            $throttledRoutes++;
            break;
        }
    }
}

if ($throttledRoutes >= 4) {
    echo "   ✅ Rate limiting applied to $throttledRoutes routes\n";
    $passed++;
} else {
    echo "   ❌ Rate limiting not fully implemented\n";
    $failed++;
}

echo "\n";

// Check 3: Authorization Policies
echo "3️⃣  Checking Authorization Policies...\n";
$policies = [
    'CustomerPolicy' => 'App\\Policies\\CustomerPolicy',
    'VehiclePolicy' => 'App\\Policies\\VehiclePolicy',
    'InvoicePolicy' => 'App\\Policies\\InvoicePolicy',
    'JobCardPolicy' => 'App\\Policies\\JobCardPolicy',
    'AppointmentPolicy' => 'App\\Policies\\AppointmentPolicy',
];

$policiesFound = 0;
foreach ($policies as $name => $class) {
    if (class_exists($class)) {
        echo "   ✅ $name exists\n";
        $policiesFound++;
    } else {
        echo "   ❌ $name missing\n";
    }
}

if ($policiesFound === 5) {
    $passed++;
} else {
    $failed++;
}

echo "\n";

// Check 4: Backup Configuration
echo "4️⃣  Checking Backup System...\n";
if (class_exists('Spatie\\Backup\\BackupServiceProvider')) {
    echo "   ✅ Spatie Backup package installed\n";
    $passed++;
} else {
    echo "   ❌ Spatie Backup package not installed\n";
    $failed++;
}

if (file_exists(config_path('backup.php'))) {
    echo "   ✅ Backup configuration exists\n";
    $passed++;
} else {
    echo "   ❌ Backup configuration missing\n";
    $failed++;
}

echo "\n";

// Check 5: Database Indexes
echo "5️⃣  Checking Database Indexes...\n";
try {
    $pdo = DB::connection()->getPdo();
    
    // Check a few key indexes
    $indexes = [
        ['customers', 'customers_phone_index'],
        ['vehicles', 'vehicles_registration_index'],
        ['appointments', 'appointments_scheduled_date_index'],
        ['job_cards', 'job_cards_number_index'],
        ['invoices', 'invoices_number_index'],
    ];
    
    $indexesFound = 0;
    foreach ($indexes as [$table, $index]) {
        $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
        if (count($result) > 0) {
            $indexesFound++;
        }
    }
    
    if ($indexesFound >= 4) {
        echo "   ✅ Performance indexes applied ({$indexesFound}/5 checked)\n";
        $passed++;
    } else {
        echo "   ⚠️  Some indexes missing ({$indexesFound}/5 found)\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "   ❌ Could not verify indexes: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n";
echo str_repeat("=", 50) . "\n";
echo "\n📊 RESULTS:\n";
echo "   ✅ Passed: $passed\n";
echo "   ❌ Failed: $failed\n\n";

if ($failed === 0) {
    echo "🎉 ALL CHECKS PASSED - System is production ready!\n\n";
    exit(0);
} else {
    echo "⚠️  Some checks failed - Please review before deployment\n\n";
    exit(1);
}
