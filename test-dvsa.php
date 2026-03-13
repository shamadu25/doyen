<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing DVSA OAuth2 Connection...\n\n";

$dvsa = new App\Services\DvsaService();

echo "Attempting to fetch MOT history for MT58FLA...\n";
$result = $dvsa->getMotHistory('MT58FLA');

if ($result) {
    echo "✅ SUCCESS! MOT History retrieved:\n\n";
    print_r($result);
} else {
    echo "❌ FAILED! Check storage/logs/laravel.log for details\n";
    
    // Try to get token directly
    echo "\nAttempting to get OAuth2 token...\n";
    
    $reflection = new ReflectionClass($dvsa);
    $method = $reflection->getMethod('getAccessToken');
    $method->setAccessible(true);
    $token = $method->invoke($dvsa);
    
    if ($token) {
        echo "✅ OAuth2 token obtained successfully!\n";
        echo "Token (first 50 chars): " . substr($token, 0, 50) . "...\n";
    } else {
        echo "❌ Failed to obtain OAuth2 token\n";
    }
}
