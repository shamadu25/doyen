<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Vehicles table columns:\n";
$columns = Schema::getColumnListing('vehicles');
foreach ($columns as $column) {
    echo "  - $column\n";
}

echo "\nLooking for photos columns:\n";
echo "  photos: " . (in_array('photos', $columns) ? '✓ EXISTS' : '✗ MISSING') . "\n";
echo "  main_photo: " . (in_array('main_photo', $columns) ? '✓ EXISTS' : '✗ MISSING') . "\n";
