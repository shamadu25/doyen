<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║         GARAGE MANAGEMENT SYSTEM - COMPREHENSIVE TEST         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$results = [];

// Test 1: Database Connection
echo "🔍 Testing Database Connection... ";
try {
    DB::connection()->getPdo();
    $results['database'] = '✅ PASSED';
    echo "✅ PASSED\n";
} catch (Exception $e) {
    $results['database'] = '❌ FAILED: ' . $e->getMessage();
    echo "❌ FAILED\n";
}

// Test 2: Database Tables
echo "🔍 Testing Database Tables... ";
try {
    $tables = [
        'users', 'customers', 'vehicles', 'appointments', 
        'job_cards', 'invoices', 'services', 'parts', 'mot_tests',
        'payments', 'quotes', 'service_reminders', 'parts_orders'
    ];
    
    $allExist = true;
    foreach ($tables as $table) {
        if (!Schema::hasTable($table)) {
            $allExist = false;
            break;
        }
    }
    
    if ($allExist) {
        $results['tables'] = '✅ PASSED (' . count($tables) . ' tables)';
        echo "✅ PASSED (" . count($tables) . " tables)\n";
    } else {
        $results['tables'] = '❌ FAILED: Missing tables';
        echo "❌ FAILED\n";
    }
} catch (Exception $e) {
    $results['tables'] = '❌ FAILED: ' . $e->getMessage();
    echo "❌ FAILED\n";
}

// Test 3: Models
echo "🔍 Testing Models... ";
try {
    $models = [
        'App\Models\User',
        'App\Models\Customer',
        'App\Models\Vehicle',
        'App\Models\Appointment',
        'App\Models\JobCard',
        'App\Models\Invoice',
        'App\Models\Service',
        'App\Models\Part',
        'App\Models\MotTest',
    ];
    
    $allExist = true;
    foreach ($models as $model) {
        if (!class_exists($model)) {
            $allExist = false;
            break;
        }
    }
    
    if ($allExist) {
        $results['models'] = '✅ PASSED (' . count($models) . ' models)';
        echo "✅ PASSED (" . count($models) . " models)\n";
    } else {
        $results['models'] = '❌ FAILED: Missing models';
        echo "❌ FAILED\n";
    }
} catch (Exception $e) {
    $results['models'] = '❌ FAILED: ' . $e->getMessage();
    echo "❌ FAILED\n";
}

// Test 4: Routes
echo "🔍 Testing Routes... ";
try {
    $routeCount = count(Route::getRoutes());
    if ($routeCount > 0) {
        $results['routes'] = '✅ PASSED (' . $routeCount . ' routes)';
        echo "✅ PASSED ($routeCount routes)\n";
    } else {
        $results['routes'] = '❌ FAILED: No routes found';
        echo "❌ FAILED\n";
    }
} catch (Exception $e) {
    $results['routes'] = '❌ FAILED: ' . $e->getMessage();
    echo "❌ FAILED\n";
}

// Test 5: Controllers
echo "🔍 Testing Controllers... ";
try {
    $controllers = [
        'App\Http\Controllers\DashboardController',
        'App\Http\Controllers\CustomerController',
        'App\Http\Controllers\VehicleController',
        'App\Http\Controllers\AppointmentController',
        'App\Http\Controllers\JobCardController',
        'App\Http\Controllers\InvoiceController',
    ];
    
    $allExist = true;
    foreach ($controllers as $controller) {
        if (!class_exists($controller)) {
            $allExist = false;
            break;
        }
    }
    
    if ($allExist) {
        $results['controllers'] = '✅ PASSED (' . count($controllers) . ' controllers)';
        echo "✅ PASSED (" . count($controllers) . " controllers)\n";
    } else {
        $results['controllers'] = '❌ FAILED: Missing controllers';
        echo "❌ FAILED\n";
    }
} catch (Exception $e) {
    $results['controllers'] = '❌ FAILED: ' . $e->getMessage();
    echo "❌ FAILED\n";
}

// Test 6: Environment Configuration
echo "🔍 Testing Environment Configuration... ";
try {
    $requiredEnv = ['APP_NAME', 'APP_ENV', 'APP_KEY', 'DB_CONNECTION', 'DB_DATABASE'];
    $allSet = true;
    
    foreach ($requiredEnv as $var) {
        if (!env($var)) {
            $allSet = false;
            break;
        }
    }
    
    if ($allSet) {
        $results['env'] = '✅ PASSED';
        echo "✅ PASSED\n";
    } else {
        $results['env'] = '⚠️  WARNING: Some environment variables not set';
        echo "⚠️  WARNING\n";
    }
} catch (Exception $e) {
    $results['env'] = '❌ FAILED: ' . $e->getMessage();
    echo "❌ FAILED\n";
}

// Test 7: File System
echo "🔍 Testing File System... ";
try {
    $dirs = ['storage/app', 'storage/logs', 'storage/framework'];
    $allExist = true;
    
    foreach ($dirs as $dir) {
        if (!is_dir(base_path($dir))) {
            $allExist = false;
            break;
        }
    }
    
    if ($allExist) {
        $results['filesystem'] = '✅ PASSED';
        echo "✅ PASSED\n";
    } else {
        $results['filesystem'] = '❌ FAILED: Missing directories';
        echo "❌ FAILED\n";
    }
} catch (Exception $e) {
    $results['filesystem'] = '❌ FAILED: ' . $e->getMessage();
    echo "❌ FAILED\n";
}

// Test 8: Services Configuration
echo "🔍 Testing Services Configuration... ";
try {
    $services = config('services');
    if (!empty($services)) {
        $results['services'] = '✅ PASSED';
        echo "✅ PASSED\n";
    } else {
        $results['services'] = '⚠️  WARNING: No services configured';
        echo "⚠️  WARNING\n";
    }
} catch (Exception $e) {
    $results['services'] = '❌ FAILED: ' . $e->getMessage();
    echo "❌ FAILED\n";
}

// Summary
echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║                       TEST SUMMARY                           ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;
$warnings = 0;

foreach ($results as $test => $result) {
    echo str_pad($test, 20) . ": " . $result . "\n";
    if (strpos($result, '✅') !== false) $passed++;
    elseif (strpos($result, '❌') !== false) $failed++;
    elseif (strpos($result, '⚠️') !== false) $warnings++;
}

echo "\n";
echo "Total Tests: " . count($results) . "\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";
echo "⚠️  Warnings: $warnings\n";

if ($failed === 0) {
    echo "\n🎉 ALL CRITICAL TESTS PASSED! System is ready to use.\n";
} else {
    echo "\n⚠️  Some tests failed. Please review the errors above.\n";
}

echo "\n";
