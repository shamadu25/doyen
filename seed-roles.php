<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;

try {
    $seeder = new RolesAndPermissionsSeeder();
    $seeder->run();
    echo "\n✅ Roles and permissions seeded successfully!\n";
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
