<?php
// Doyen Auto Services - Deployment Diagnostics
// Visit: https://doyenautos.co.uk/health-check.php
// DELETE this file after deployment is confirmed working

echo "<pre style='font-family:monospace;font-size:14px;padding:20px'>";
echo "=== DOYEN AUTO - DEPLOYMENT DIAGNOSTICS ===\n\n";

// 1. PHP Version
$phpVersion = PHP_VERSION;
$phpOk = version_compare($phpVersion, '8.2.0', '>=');
echo ($phpOk ? "✅" : "❌") . " PHP Version: $phpVersion" . ($phpOk ? "" : " (Need 8.2+)") . "\n";

// 2. .env file
$envExists = file_exists(__DIR__ . '/../.env');
echo ($envExists ? "✅" : "❌") . " .env file: " . ($envExists ? "Found" : "MISSING - upload your .env file!") . "\n";

// 3. vendor/autoload.php
$vendorExists = file_exists(__DIR__ . '/../vendor/autoload.php');
echo ($vendorExists ? "✅" : "❌") . " vendor/autoload.php: " . ($vendorExists ? "Found" : "MISSING - run: composer install --no-dev") . "\n";

// 4. Storage writable
$storageWritable = is_writable(__DIR__ . '/../storage');
echo ($storageWritable ? "✅" : "❌") . " storage/ writable: " . ($storageWritable ? "Yes" : "NO - run: chmod -R 775 storage") . "\n";

// 5. bootstrap/cache writable
$cacheWritable = is_writable(__DIR__ . '/../bootstrap/cache');
echo ($cacheWritable ? "✅" : "❌") . " bootstrap/cache/ writable: " . ($cacheWritable ? "Yes" : "NO - run: chmod -R 775 bootstrap/cache") . "\n";

// 6. Read APP_KEY from .env
if ($envExists) {
    $env = file_get_contents(__DIR__ . '/../.env');
    preg_match('/^APP_KEY=(.+)$/m', $env, $keyMatch);
    $appKey = trim($keyMatch[1] ?? '');
    $keyOk = strlen($appKey) > 10 && $appKey !== '<RUN: php artisan key:generate --show>';
    echo ($keyOk ? "✅" : "❌") . " APP_KEY: " . ($keyOk ? "Set ✓" : "MISSING - run: php artisan key:generate --force") . "\n";

    // 7. DB config
    preg_match('/^DB_DATABASE=(.+)$/m', $env, $dbMatch);
    $dbName = trim($dbMatch[1] ?? '');
    $dbOk = !empty($dbName) && strpos($dbName, '<CHANGE') === false;
    echo ($dbOk ? "✅" : "❌") . " DB_DATABASE: " . ($dbOk ? $dbName : "NOT SET in .env") . "\n";

    // 8. APP_URL
    preg_match('/^APP_URL=(.+)$/m', $env, $urlMatch);
    $appUrl = trim($urlMatch[1] ?? '');
    echo "ℹ️  APP_URL: $appUrl\n";

    // 9. APP_DEBUG
    preg_match('/^APP_DEBUG=(.+)$/m', $env, $debugMatch);
    $debug = trim($debugMatch[1] ?? 'false');
    echo "ℹ️  APP_DEBUG: $debug\n";
}

// 10. Try DB connection
if ($vendorExists && $envExists) {
    try {
        require __DIR__ . '/../vendor/autoload.php';
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
        $dsn = 'mysql:host=' . ($_ENV['DB_HOST'] ?? '127.0.0.1') . ';port=' . ($_ENV['DB_PORT'] ?? 3306) . ';dbname=' . ($_ENV['DB_DATABASE'] ?? '');
        $pdo = new PDO($dsn, $_ENV['DB_USERNAME'] ?? '', $_ENV['DB_PASSWORD'] ?? '');
        echo "✅ Database connection: OK\n";
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        $migrated = $stmt->rowCount() > 0;
        echo ($migrated ? "✅" : "❌") . " Migrations: " . ($migrated ? "Run ✓" : "NOT run - run: php artisan migrate --force") . "\n";
    } catch (Exception $e) {
        echo "❌ Database connection: FAILED\n";
        echo "   Error: " . $e->getMessage() . "\n";
    }
}

// 11. Storage symlink
$symlinkExists = file_exists(__DIR__ . '/storage') && is_link(__DIR__ . '/storage');
echo ($symlinkExists ? "✅" : "❌") . " Storage symlink: " . ($symlinkExists ? "OK" : "MISSING - run: php artisan storage:link") . "\n";

echo "\n=== END DIAGNOSTICS ===\n";
echo "</pre>";
