<?php
/**
 * TEMPORARY SERVER DIAGNOSTIC SCRIPT
 * Upload to: public/server-debug.php
 * Visit: https://doyen.cliqpos.com/server-debug.php
 * DELETE THIS FILE after diagnosing!
 */

// Basic security - remove in production after use
$allowed_ips = ['127.0.0.1']; // Add your IP if needed, or remove this check temporarily
// Uncomment below to restrict by IP:
// if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowed_ips)) { die('Access denied'); }

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo '<!DOCTYPE html><html><head><title>Server Debug</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#e0e0e0;}
.ok{color:#4caf50;}.fail{color:#f44336;}.warn{color:#ff9800;}.section{background:#2d2d2d;padding:15px;margin:10px 0;border-radius:5px;}
h2{color:#64b5f6;}pre{background:#111;padding:10px;overflow:auto;}</style></head><body>';
echo '<h1 style="color:#fff;">🔍 Server Diagnostic Report</h1>';
echo '<p style="color:#aaa;">Generated: ' . date('Y-m-d H:i:s') . ' UTC</p>';

// ─── PHP Environment ───────────────────────────────────────────────────────
echo '<div class="section"><h2>1. PHP Environment</h2>';
$phpVersion = phpversion();
$required = '8.2.0';
$phpOk = version_compare($phpVersion, $required, '>=');
echo '<p>PHP Version: <span class="' . ($phpOk ? 'ok' : 'fail') . '">' . $phpVersion . ($phpOk ? ' ✓' : ' ✗ (need 8.2+)') . '</span></p>';

$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'curl'];
echo '<p>Required Extensions:</p><ul>';
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    echo '<li>' . $ext . ': <span class="' . ($loaded ? 'ok' : 'fail') . '">' . ($loaded ? '✓' : '✗ MISSING') . '</span></li>';
}
echo '</ul></div>';

// ─── .env File ─────────────────────────────────────────────────────────────
echo '<div class="section"><h2>2. .env File</h2>';
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    echo '<p class="fail">✗ .env file NOT FOUND! You need to rename .env.production to .env</p>';
} else {
    echo '<p class="ok">✓ .env file exists</p>';
    
    // Parse .env safely (only show non-sensitive keys)
    $envContent = file_get_contents($envPath);
    $lines = explode("\n", $envContent);
    $safeKeys = ['APP_NAME', 'APP_ENV', 'APP_DEBUG', 'APP_URL', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'SESSION_DRIVER', 'CACHE_STORE', 'QUEUE_CONNECTION'];
    
    echo '<p>Key settings:</p><ul>';
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2 && in_array($parts[0], $safeKeys)) {
            $key = $parts[0];
            $value = $parts[1];
            
            $isWarning = false;
            $warningMsg = '';
            
            if ($key === 'APP_DEBUG' && strtolower(trim($value, '"\'')) === 'true') {
                $isWarning = true;
                $warningMsg = ' ⚠ Should be false in production';
            }
            if ($key === 'DB_USERNAME' && trim($value, '"\'') === 'root') {
                $isWarning = true;
                $warningMsg = ' ⚠ root user rarely works on shared hosting!';
            }
            if ($key === 'DB_PASSWORD' && empty(trim($value, '"\'')) && $key === 'DB_PASSWORD') {
                $isWarning = true;
                $warningMsg = ' ⚠ Empty password - update with your hosting DB password!';
            }
            if ($key === 'APP_URL' && (str_contains($value, 'localhost') || str_contains($value, '127.0.0.1'))) {
                $isWarning = true;
                $warningMsg = ' ⚠ Still pointing to localhost!';
            }
            
            $class = $isWarning ? 'warn' : 'ok';
            echo '<li>' . htmlspecialchars($key) . '=<span class="' . $class . '">' . htmlspecialchars($value) . $warningMsg . '</span></li>';
        }
    }
    echo '</ul>';
    
    // Check APP_KEY
    if (str_contains($envContent, 'APP_KEY=') && !str_contains($envContent, 'APP_KEY=base64:') && !preg_match('/APP_KEY=base64:[A-Za-z0-9+\/=]+/', $envContent)) {
        echo '<p class="fail">✗ APP_KEY is missing or invalid! Run: php artisan key:generate --force</p>';
    } elseif (preg_match('/APP_KEY=(base64:[A-Za-z0-9+\/=]+)/', $envContent)) {
        echo '<p class="ok">✓ APP_KEY is set</p>';
    }
}
echo '</div>';

// ─── Database Connection ───────────────────────────────────────────────────
echo '<div class="section"><h2>3. Database Connection</h2>';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    preg_match('/^DB_HOST=(.*)$/m', $envContent, $hostMatch);
    preg_match('/^DB_PORT=(.*)$/m', $envContent, $portMatch);
    preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $dbMatch);
    preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $userMatch);
    preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $passMatch);
    
    $host = trim($hostMatch[1] ?? '127.0.0.1', '"\'');
    $port = trim($portMatch[1] ?? '3306', '"\'');
    $database = trim($dbMatch[1] ?? '', '"\'');
    $username = trim($userMatch[1] ?? '', '"\'');
    $password = trim($passMatch[1] ?? '', '"\'');
    
    echo '<p>Attempting connection to: <b>' . htmlspecialchars($username) . '@' . htmlspecialchars($host) . ':' . htmlspecialchars($port) . '/' . htmlspecialchars($database) . '</b></p>';
    
    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo '<p class="ok">✓ Database connected successfully!</p>';
        
        // Check critical tables
        $criticalTables = ['users', 'migrations', 'sessions', 'cache', 'jobs', 'roles', 'permissions'];
        echo '<p>Checking critical tables:</p><ul>';
        foreach ($criticalTables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            $exists = $stmt->rowCount() > 0;
            $class = $exists ? 'ok' : 'warn';
            $note = '';
            if (!$exists) {
                if ($table === 'sessions') $note = ' ← Run: php artisan migrate (needed for SESSION_DRIVER=database)';
                elseif ($table === 'cache') $note = ' ← Run: php artisan migrate (needed for CACHE_STORE=database)';
                elseif ($table === 'jobs') $note = ' ← Run: php artisan migrate (needed for QUEUE_CONNECTION=database)';
                elseif ($table === 'migrations') $note = ' ← Migrations NOT run yet!';
                elseif ($table === 'roles') $note = ' ← Run seeder: php artisan db:seed --class=RolesAndPermissionsSeeder --force';
            }
            echo '<li>' . $table . ': <span class="' . $class . '">' . ($exists ? '✓ exists' : '✗ MISSING' . $note) . '</span></li>';
        }
        echo '</ul>';
    } catch (PDOException $e) {
        echo '<p class="fail">✗ Database connection FAILED: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p class="warn">→ Update DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD in your .env file with your hosting credentials</p>';
    }
} else {
    echo '<p class="fail">✗ Cannot check - .env not found</p>';
}
echo '</div>';

// ─── File Permissions ──────────────────────────────────────────────────────
echo '<div class="section"><h2>4. File & Directory Permissions</h2>';
$checkPaths = [
    __DIR__ . '/../storage' => 'storage/',
    __DIR__ . '/../storage/logs' => 'storage/logs/',
    __DIR__ . '/../storage/framework' => 'storage/framework/',
    __DIR__ . '/../storage/framework/cache' => 'storage/framework/cache/',
    __DIR__ . '/../storage/framework/sessions' => 'storage/framework/sessions/',
    __DIR__ . '/../storage/framework/views' => 'storage/framework/views/',
    __DIR__ . '/../bootstrap/cache' => 'bootstrap/cache/',
];

foreach ($checkPaths as $path => $label) {
    $exists = file_exists($path);
    $writable = $exists && is_writable($path);
    $class = $writable ? 'ok' : ($exists ? 'fail' : 'fail');
    $status = !$exists ? '✗ MISSING' : ($writable ? '✓ writable' : '✗ NOT writable (chmod 775)');
    echo '<p>' . $label . ': <span class="' . $class . '">' . $status . '</span></p>';
}
echo '</div>';

// ─── Vendor Autoloader ─────────────────────────────────────────────────────
echo '<div class="section"><h2>5. Vendor / Autoloader</h2>';
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($vendorAutoload)) {
    echo '<p class="fail">✗ vendor/autoload.php NOT FOUND! Run: composer install --no-dev --optimize-autoloader</p>';
} else {
    echo '<p class="ok">✓ vendor/autoload.php exists</p>';
    
    try {
        require $vendorAutoload;
        echo '<p class="ok">✓ Autoloader loaded successfully</p>';
    } catch (Throwable $e) {
        echo '<p class="fail">✗ Autoloader error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}
echo '</div>';

// ─── Bootstrap App ─────────────────────────────────────────────────────────
echo '<div class="section"><h2>6. Laravel Bootstrap</h2>';
if (file_exists($vendorAutoload)) {
    try {
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        echo '<p class="ok">✓ Laravel bootstrapped successfully</p>';
        echo '<p>Laravel Version: <span class="ok">' . \Illuminate\Foundation\Application::VERSION . '</span></p>';
    } catch (Throwable $e) {
        echo '<p class="fail">✗ Bootstrap error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
}
echo '</div>';

// ─── Build Assets ──────────────────────────────────────────────────────────
echo '<div class="section"><h2>7. Frontend Build Assets</h2>';
$buildDir = __DIR__ . '/build';
if (!is_dir($buildDir)) {
    echo '<p class="fail">✗ /public/build/ directory missing! Run: npm run build</p>';
} else {
    $manifestPath = $buildDir . '/manifest.json';
    $vitePath = $buildDir . '/.vite/manifest.json';
    
    if (file_exists($manifestPath) || file_exists($vitePath)) {
        echo '<p class="ok">✓ Build manifest found</p>';
    } else {
        echo '<p class="warn">⚠ No manifest.json in /public/build/ - assets may not load</p>';
    }
    
    $cssFiles = glob($buildDir . '/assets/*.css');
    $jsFiles = glob($buildDir . '/assets/*.js');
    echo '<p>CSS files: <span class="' . (count($cssFiles) > 0 ? 'ok' : 'fail') . '">' . count($cssFiles) . ' found</span></p>';
    echo '<p>JS files: <span class="' . (count($jsFiles) > 0 ? 'ok' : 'fail') . '">' . count($jsFiles) . ' found</span></p>';
}
echo '</div>';

// ─── Error Log ─────────────────────────────────────────────────────────────
echo '<div class="section"><h2>8. Recent Laravel Error Log</h2>';
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $size = filesize($logFile);
    echo '<p>Log size: ' . number_format($size / 1024, 1) . ' KB</p>';
    if ($size > 0) {
        // Get last 100 lines
        $lines = file($logFile);
        $lastLines = array_slice($lines, -50);
        echo '<p class="warn">Last 50 lines of laravel.log:</p>';
        echo '<pre style="font-size:11px;max-height:400px;overflow:auto;">' . htmlspecialchars(implode('', $lastLines)) . '</pre>';
    } else {
        echo '<p class="ok">✓ Log file is empty (no errors yet)</p>';
    }
} else {
    echo '<p class="warn">⚠ No log file found. Storage may not be writable.</p>';
}
echo '</div>';

// ─── Summary & Next Steps ──────────────────────────────────────────────────
echo '<div class="section"><h2>9. Quick Fix Commands (run via SSH/cPanel Terminal)</h2>';
echo '<pre>
# 1. Navigate to your app root
cd /home/YOUR_USERNAME/YOUR_APP_PATH

# 2. Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R $(whoami):$(whoami) storage bootstrap/cache

# 3. Generate app key (if missing)
php artisan key:generate --force

# 4. Run database migrations
php artisan migrate --force

# 5. Seed roles & permissions
php artisan db:seed --class=RolesAndPermissionsSeeder --force

# 6. Create storage symlink
php artisan storage:link

# 7. Clear & rebuild caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
</pre>';
echo '</div>';

echo '<p style="color:#f44336;margin-top:30px;"><strong>⚠ DELETE THIS FILE after diagnosing: public/server-debug.php</strong></p>';
echo '</body></html>';
