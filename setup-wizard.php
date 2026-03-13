#!/usr/bin/env php
<?php

/**
 * UK Garage Management System - Setup Wizard
 * Interactive setup script for first-time installation
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                                                            ║\n";
echo "║        UK Garage Management System - Setup Wizard         ║\n";
echo "║                                                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Colors for terminal output
define('GREEN', "\033[32m");
define('RED', "\033[31m");
define('YELLOW', "\033[33m");
define('BLUE', "\033[34m");
define('RESET', "\033[0m");

// Check if we're in the Laravel directory
if (!file_exists('artisan')) {
    echo RED . "ERROR: Please run this script from the Laravel project root directory\n" . RESET;
    exit(1);
}

// Step 1: Check Requirements
echo BLUE . "[Step 1/7] Checking Requirements...\n" . RESET;
echo "─────────────────────────────────────\n";

$requirements = [
    'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
    'Composer' => checkCommand('composer --version'),
    'Node.js' => checkCommand('node --version'),
    'NPM' => checkCommand('npm --version'),
    'MySQL' => checkCommand('mysql --version'),
];

foreach ($requirements as $req => $status) {
    echo ($status ? GREEN . '✓' : RED . '✗') . " {$req}" . RESET . "\n";
    if (!$status && $req !== 'Composer') {
        echo RED . "ERROR: {$req} is required\n" . RESET;
        exit(1);
    }
}

echo GREEN . "\n✓ All requirements met!\n\n" . RESET;

// Step 2: Generate Application Key
echo BLUE . "[Step 2/7] Generating Application Key...\n" . RESET;
echo "─────────────────────────────────────\n";

if (runCommand('php artisan key:generate --force')) {
    echo GREEN . "✓ Application key generated\n\n" . RESET;
} else {
    echo RED . "✗ Failed to generate key\n\n" . RESET;
    exit(1);
}

// Step 3: Database Configuration
echo BLUE . "[Step 3/7] Database Configuration\n" . RESET;
echo "─────────────────────────────────────\n";

echo "Enter MySQL database details:\n";
$dbName = readline("Database name [garage_management]: ") ?: 'garage_management';
$dbHost = readline("Database host [127.0.0.1]: ") ?: '127.0.0.1';
$dbPort = readline("Database port [3306]: ") ?: '3306';
$dbUser = readline("Database username [root]: ") ?: 'root';
$dbPass = readline("Database password: ");

// Create database
echo "\nCreating database...\n";
$createDbCmd = sprintf(
    "mysql -h %s -P %s -u %s %s -e \"CREATE DATABASE IF NOT EXISTS %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\"",
    $dbHost,
    $dbPort,
    $dbUser,
    $dbPass ? "-p{$dbPass}" : "",
    $dbName
);

if (runCommand($createDbCmd, false)) {
    echo GREEN . "✓ Database created\n\n" . RESET;
} else {
    echo YELLOW . "⚠ Database might already exist or check credentials\n\n" . RESET;
}

// Update .env
updateEnv([
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => $dbHost,
    'DB_PORT' => $dbPort,
    'DB_DATABASE' => $dbName,
    'DB_USERNAME' => $dbUser,
    'DB_PASSWORD' => $dbPass,
]);

echo GREEN . "✓ Database configuration updated\n\n" . RESET;

// Step 4: Garage Business Details
echo BLUE . "[Step 4/7] Garage Business Details\n" . RESET;
echo "─────────────────────────────────────\n";

echo "Enter your garage business information:\n";
$garageName = readline("Garage name: ") ?: 'My Garage';
$garageAddress = readline("Address: ") ?: '';
$garageCity = readline("City: ") ?: '';
$garagePostcode = readline("Postcode: ") ?: '';
$garagePhone = readline("Phone: ") ?: '';
$garageEmail = readline("Email: ") ?: '';
$garageVat = readline("VAT Number (optional): ") ?: '';

updateEnv([
    'APP_NAME' => "\"{$garageName}\"",
    'GARAGE_NAME' => "\"{$garageName}\"",
    'GARAGE_ADDRESS' => "\"{$garageAddress}\"",
    'GARAGE_CITY' => "\"{$garageCity}\"",
    'GARAGE_POSTCODE' => "\"{$garagePostcode}\"",
    'GARAGE_PHONE' => "\"{$garagePhone}\"",
    'GARAGE_EMAIL' => "\"{$garageEmail}\"",
    'GARAGE_VAT_NUMBER' => "\"{$garageVat}\"",
]);

echo GREEN . "\n✓ Business details saved\n\n" . RESET;

// Step 5: Run Migrations
echo BLUE . "[Step 5/7] Creating Database Tables...\n" . RESET;
echo "─────────────────────────────────────\n";

if (runCommand('php artisan migrate --force')) {
    echo GREEN . "✓ Database tables created\n\n" . RESET;
} else {
    echo RED . "✗ Migration failed\n\n" . RESET;
    exit(1);
}

// Step 6: Seed Data
echo BLUE . "[Step 6/7] Seeding Sample Services...\n" . RESET;
echo "─────────────────────────────────────\n";

if (runCommand('php artisan db:seed --class=ServiceSeeder')) {
    echo GREEN . "✓ Services seeded successfully\n\n" . RESET;
} else {
    echo YELLOW . "⚠ Seeding failed (optional)\n\n" . RESET;
}

// Step 7: Install NPM Dependencies
echo BLUE . "[Step 7/7] Installing Frontend Dependencies...\n" . RESET;
echo "─────────────────────────────────────\n";

echo "This may take a few minutes...\n";
if (runCommand('npm install')) {
    echo GREEN . "✓ NPM packages installed\n" . RESET;
    
    echo "Building assets...\n";
    if (runCommand('npm run build')) {
        echo GREEN . "✓ Assets compiled\n\n" . RESET;
    }
} else {
    echo YELLOW . "⚠ NPM installation failed (optional)\n\n" . RESET;
}

// Final Summary
echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                                                            ║\n";
echo "║                  Setup Complete! 🎉                        ║\n";
echo "║                                                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo YELLOW . "Next Steps:\n" . RESET;
echo "───────────\n";
echo "1. Review your .env file and add API keys if available:\n";
echo "   - DVLA_API_KEY (vehicle lookup)\n";
echo "   - DVSA_API_KEY (MOT history)\n";
echo "   - TECDOC_API_KEY (parts catalog)\n";
echo "\n";
echo "2. Start the development server:\n";
echo "   " . GREEN . "php artisan serve" . RESET . "\n";
echo "\n";
echo "3. Visit your application:\n";
echo "   " . BLUE . "http://localhost:8000" . RESET . "\n";
echo "\n";
echo "4. Read the documentation:\n";
echo "   - GETTING_STARTED.md\n";
echo "   - QUICK_REFERENCE.md\n";
echo "   - ENV_CONFIGURATION_GUIDE.md\n";
echo "\n";

echo GREEN . "Your garage management system is ready to use!\n" . RESET;
echo "\n";

// Helper Functions
function checkCommand($command) {
    exec($command . ' 2>&1', $output, $return);
    return $return === 0;
}

function runCommand($command, $showOutput = true) {
    if ($showOutput) {
        passthru($command, $return);
    } else {
        exec($command . ' 2>&1', $output, $return);
    }
    return $return === 0;
}

function updateEnv($values) {
    $envFile = '.env';
    if (!file_exists($envFile)) {
        copy('.env.example', $envFile);
    }
    
    $content = file_get_contents($envFile);
    
    foreach ($values as $key => $value) {
        $pattern = "/^{$key}=.*/m";
        $replacement = "{$key}={$value}";
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
        } else {
            $content .= "\n{$replacement}";
        }
    }
    
    file_put_contents($envFile, $content);
}
