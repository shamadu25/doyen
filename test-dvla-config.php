<?php
/**
 * Quick DVLA Configuration Test
 * This script verifies that the DVLA API key is properly configured
 */

// Load environment variables
$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    die("❌ .env file not found\n");
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];

foreach ($lines as $line) {
    // Skip comments
    if (strpos(trim($line), '#') === 0) {
        continue;
    }
    
    if (strpos($line, '=') !== false) {
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

echo "\n=== DVLA API Configuration Test ===\n\n";

// Check DVLA API Key
if (isset($env['DVLA_API_KEY']) && !empty($env['DVLA_API_KEY'])) {
    echo "✅ DVLA_API_KEY: Configured\n";
    echo "   Key (masked): " . substr($env['DVLA_API_KEY'], 0, 8) . "..." . substr($env['DVLA_API_KEY'], -4) . "\n";
} else {
    echo "❌ DVLA_API_KEY: Not configured\n";
}

// Check DVLA API URL
if (isset($env['DVLA_API_URL']) && !empty($env['DVLA_API_URL'])) {
    echo "✅ DVLA_API_URL: " . $env['DVLA_API_URL'] . "\n";
} else {
    echo "❌ DVLA_API_URL: Not configured\n";
}

echo "\n=== Test Complete ===\n\n";
