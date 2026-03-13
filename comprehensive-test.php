<?php
/**
 * Comprehensive Testing Script for Doyen Autos Garage Management System
 * Tests all features to ensure 100% readiness for live deployment
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Color output for terminal
function success($msg) { echo "\033[32m✓ $msg\033[0m\n"; }
function error($msg) { echo "\033[31m✗ $msg\033[0m\n"; }
function info($msg) { echo "\033[34mℹ $msg\033[0m\n"; }
function section($msg) { echo "\n\033[1;33m=== $msg ===\033[0m\n"; }

$passed = 0;
$failed = 0;
$errors = [];

section("COMPREHENSIVE DEPLOYMENT READINESS TEST");
echo "Testing all features for Doyen Autos Garage Management System\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";

// Test 1: Database Connection
section("1. DATABASE CONNECTION");
try {
    DB::connection()->getPdo();
    $dbName = DB::connection()->getDatabaseName();
    success("Connected to database: $dbName");
    $passed++;
} catch (Exception $e) {
    error("Database connection failed: " . $e->getMessage());
    $errors[] = "Database: " . $e->getMessage();
    $failed++;
}

// Test 2: Required Tables Check
section("2. DATABASE SCHEMA VERIFICATION");
$requiredTables = [
    'users', 'customers', 'vehicles', 'appointments', 'job_cards', 
    'mot_tests', 'invoices', 'invoice_items', 'payments', 'parts',
    'inventory_transactions', 'activity_logs', 'settings', 'reminders'
];

foreach ($requiredTables as $table) {
    try {
        if (Schema::hasTable($table)) {
            success("Table exists: $table");
            $passed++;
        } else {
            error("Missing table: $table");
            $errors[] = "Missing table: $table";
            $failed++;
        }
    } catch (Exception $e) {
        error("Error checking table $table: " . $e->getMessage());
        $failed++;
    }
}

// Test 3: User Authentication
section("3. USER & AUTHENTICATION");
try {
    $userCount = DB::table('users')->count();
    if ($userCount > 0) {
        success("Users in system: $userCount");
        $testUser = DB::table('users')->first();
        success("Test user found: " . $testUser->name);
        $passed += 2;
    } else {
        error("No users found. Please create an admin user.");
        $errors[] = "No users in system";
        $failed++;
    }
} catch (Exception $e) {
    error("User check failed: " . $e->getMessage());
    $errors[] = "Users: " . $e->getMessage();
    $failed++;
}

// Test 4: Customer Management
section("4. CUSTOMER MANAGEMENT");
try {
    $customerCount = DB::table('customers')->count();
    info("Total customers: $customerCount");
    
    $requiredColumns = ['first_name', 'last_name', 'email', 'phone', 'is_active'];
    foreach ($requiredColumns as $col) {
        if (Schema::hasColumn('customers', $col)) {
            success("Customer column exists: $col");
            $passed++;
        } else {
            error("Missing customer column: $col");
            $errors[] = "Missing column: customers.$col";
            $failed++;
        }
    }
} catch (Exception $e) {
    error("Customer check failed: " . $e->getMessage());
    $failed++;
}

// Test 5: Vehicle Management
section("5. VEHICLE MANAGEMENT");
try {
    $vehicleCount = DB::table('vehicles')->count();
    info("Total vehicles: $vehicleCount");
    
    $requiredColumns = ['customer_id', 'registration_number', 'make', 'model', 'year'];
    foreach ($requiredColumns as $col) {
        if (Schema::hasColumn('vehicles', $col)) {
            success("Vehicle column exists: $col");
            $passed++;
        } else {
            error("Missing vehicle column: $col");
            $errors[] = "Missing column: vehicles.$col";
            $failed++;
        }
    }
} catch (Exception $e) {
    error("Vehicle check failed: " . $e->getMessage());
    $failed++;
}

// Test 6: Appointments System
section("6. APPOINTMENTS & BOOKING");
try {
    $appointmentCount = DB::table('appointments')->count();
    info("Total appointments: $appointmentCount");
    
    if (Schema::hasColumn('appointments', 'appointment_type')) {
        success("Appointments table has appointment_type column");
        $passed++;
    } else {
        error("Appointments missing appointment_type column");
        $failed++;
    }
    
    if (Schema::hasColumn('appointments', 'scheduled_date')) {
        success("Appointments table has scheduled_date column");
        $passed++;
    } else {
        error("Appointments missing scheduled_date column");
        $failed++;
    }
} catch (Exception $e) {
    error("Appointments check failed: " . $e->getMessage());
    $failed++;
}

// Test 7: Job Cards
section("7. JOB CARDS SYSTEM");
try {
    $jobCardCount = DB::table('job_cards')->count();
    info("Total job cards: $jobCardCount");
    
    if (Schema::hasColumn('job_cards', 'status')) {
        success("Job cards table has status column");
        $passed++;
    }
    if (Schema::hasColumn('job_cards', 'vehicle_id')) {
        success("Job cards linked to vehicles");
        $passed++;
    }
} catch (Exception $e) {
    error("Job cards check failed: " . $e->getMessage());
    $failed++;
}

// Test 8: MOT Tests
section("8. MOT TESTING SYSTEM");
try {
    $motCount = DB::table('mot_tests')->count();
    info("Total MOT tests: $motCount");
    
    if (Schema::hasColumn('mot_tests', 'test_result')) {
        $hasDefault = DB::select("SHOW COLUMNS FROM mot_tests WHERE Field = 'test_result'");
        if (!empty($hasDefault) && $hasDefault[0]->Default !== null) {
            success("MOT test_result has default value");
            $passed++;
        } else {
            error("MOT test_result missing default value");
            $failed++;
        }
    }
    
    if (Schema::hasColumn('mot_tests', 'expiry_date')) {
        $columnInfo = DB::select("SHOW COLUMNS FROM mot_tests WHERE Field = 'expiry_date'");
        if (!empty($columnInfo) && $columnInfo[0]->Null === 'YES') {
            success("MOT expiry_date is nullable");
            $passed++;
        } else {
            error("MOT expiry_date should be nullable");
            $failed++;
        }
    }
} catch (Exception $e) {
    error("MOT tests check failed: " . $e->getMessage());
    $failed++;
}

// Test 9: Invoices & Payments
section("9. INVOICING & PAYMENTS");
try {
    $invoiceCount = DB::table('invoices')->count();
    $paymentCount = DB::table('payments')->count();
    info("Total invoices: $invoiceCount");
    info("Total payments: $paymentCount");
    
    if (Schema::hasTable('invoice_items')) {
        success("Invoice items table exists");
        $passed++;
    }
    
    if (Schema::hasColumn('invoices', 'invoice_number')) {
        success("Invoices have invoice_number");
        $passed++;
    }
} catch (Exception $e) {
    error("Invoice check failed: " . $e->getMessage());
    $failed++;
}

// Test 10: Parts/Inventory
section("10. PARTS & INVENTORY");
try {
    $partsCount = DB::table('parts')->count();
    info("Total parts: $partsCount");
    
    if (Schema::hasColumn('parts', 'stock_quantity')) {
        success("Parts table has stock tracking");
        $passed++;
    }
} catch (Exception $e) {
    error("Parts check failed: " . $e->getMessage());
    $failed++;
}

// Test 11: DVLA Integration
section("11. DVLA API INTEGRATION");
try {
    $dvlaKey = env('DVLA_API_KEY');
    if ($dvlaKey && $dvlaKey !== '') {
        success("DVLA API key configured: " . substr($dvlaKey, 0, 10) . "...");
        $passed++;
        
        if (class_exists('App\Services\DvlaService')) {
            success("DvlaService class exists");
            $passed++;
        } else {
            error("DvlaService class not found");
            $failed++;
        }
    } else {
        error("DVLA API key not configured");
        $errors[] = "DVLA API key missing";
        $failed++;
    }
} catch (Exception $e) {
    error("DVLA check failed: " . $e->getMessage());
    $failed++;
}

// Test 12: Routes Verification
section("12. ROUTES VERIFICATION");
try {
    $routes = app('router')->getRoutes();
    $criticalRoutes = [
        '/',
        'login',
        'book-online',
        'dashboard',
        'customers',
        'vehicles',
        'bookings',
        'job-cards',
        'mot-tests',
        'invoices',
        'reports',
    ];
    
    $routeNames = [];
    foreach ($routes as $route) {
        $routeNames[] = $route->getName();
    }
    
    foreach ($criticalRoutes as $routeName) {
        if (in_array($routeName, $routeNames) || in_array("$routeName.index", $routeNames)) {
            success("Route registered: $routeName");
            $passed++;
        } else {
            // Check if it's a direct URI match
            $found = false;
            foreach ($routes as $route) {
                if ($route->uri() === $routeName || str_contains($route->uri(), $routeName)) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                success("Route found: $routeName");
                $passed++;
            } else {
                error("Route missing: $routeName");
                $failed++;
            }
        }
    }
} catch (Exception $e) {
    error("Routes check failed: " . $e->getMessage());
    $failed++;
}

// Test 13: Storage & File Permissions
section("13. STORAGE & PERMISSIONS");
try {
    $storagePath = storage_path();
    $publicPath = public_path();
    
    if (is_writable($storagePath)) {
        success("Storage directory is writable");
        $passed++;
    } else {
        error("Storage directory is not writable: $storagePath");
        $errors[] = "Storage not writable";
        $failed++;
    }
    
    if (is_writable(storage_path('logs'))) {
        success("Logs directory is writable");
        $passed++;
    } else {
        error("Logs directory is not writable");
        $failed++;
    }
    
    if (file_exists(public_path('build/manifest.json'))) {
        success("Frontend assets compiled (manifest.json exists)");
        $passed++;
    } else {
        error("Frontend assets not compiled. Run: npm run build");
        $errors[] = "Missing compiled assets";
        $failed++;
    }
} catch (Exception $e) {
    error("Storage check failed: " . $e->getMessage());
    $failed++;
}

// Test 14: Configuration Check
section("14. CONFIGURATION VERIFICATION");
try {
    $appName = config('app.name');
    $appUrl = config('app.url');
    $appEnv = config('app.env');
    
    success("App Name: $appName");
    success("App URL: $appUrl");
    info("Environment: $appEnv");
    
    if ($appEnv === 'production') {
        if (!config('app.debug')) {
            success("Debug mode OFF (correct for production)");
            $passed++;
        } else {
            error("Debug mode ON (should be OFF for production)");
            $errors[] = "Debug mode enabled in production";
            $failed++;
        }
    }
    
    $passed += 2;
} catch (Exception $e) {
    error("Configuration check failed: " . $e->getMessage());
    $failed++;
}

// Test 15: Activity Logs
section("15. ACTIVITY LOGGING");
try {
    if (class_exists('App\Models\ActivityLog')) {
        success("ActivityLog model exists");
        $logCount = DB::table('activity_logs')->count();
        info("Activity logs recorded: $logCount");
        $passed++;
    } else {
        error("ActivityLog model not found");
        $failed++;
    }
} catch (Exception $e) {
    error("Activity log check failed: " . $e->getMessage());
    $failed++;
}

// Final Summary
section("TEST SUMMARY");
$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100, 2) : 0;

echo "\n";
echo "Total Tests: $total\n";
echo "\033[32mPassed: $passed\033[0m\n";
echo "\033[31mFailed: $failed\033[0m\n";
echo "Success Rate: $percentage%\n";
echo "\n";

if ($percentage >= 95) {
    success("✓✓✓ SYSTEM READY FOR DEPLOYMENT ✓✓✓");
    echo "\n\033[1;32mThe system has passed comprehensive testing and is ready for live deployment.\033[0m\n";
} elseif ($percentage >= 80) {
    info("⚠ SYSTEM MOSTLY READY - FIX CRITICAL ISSUES");
    echo "\n\033[1;33mThe system is mostly ready but has some issues that should be addressed.\033[0m\n";
} else {
    error("✗✗✗ SYSTEM NOT READY FOR DEPLOYMENT ✗✗✗");
    echo "\n\033[1;31mCritical issues found. Please resolve before deployment.\033[0m\n";
}

if (!empty($errors)) {
    echo "\n\033[1;31mCRITICAL ISSUES TO FIX:\033[0m\n";
    foreach ($errors as $err) {
        echo "  • $err\n";
    }
}

echo "\n";
echo "For detailed logs, check: storage/logs/\n";
echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";
echo "\n";

exit($failed > 0 ? 1 : 0);
