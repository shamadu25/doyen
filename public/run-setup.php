<?php
/**
 * TEMPORARY SETUP SCRIPT FOR PRODUCTION
 * ----------------------------------------
 * Visit: https://doyen.cliqpos.com/run-setup.php
 * 
 * ⚠️  DELETE THIS FILE IMMEDIATELY after running!
 * It's a security risk to leave it on the server.
 */

// Simple password protection - change this before uploading
$secret = 'setup2026';
if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    die('<h2>Access Denied</h2><p>Provide ?key=setup2026 in URL to proceed.</p>');
}

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(120);

echo '<!DOCTYPE html><html><head><title>Production Setup</title>';
echo '<style>
body{font-family:monospace;padding:30px;background:#1a1a1a;color:#e0e0e0;max-width:900px;margin:auto;}
.ok{color:#4caf50;font-weight:bold;}
.fail{color:#f44336;font-weight:bold;}
.warn{color:#ff9800;font-weight:bold;}
.section{background:#2d2d2d;padding:20px;margin:15px 0;border-radius:8px;border-left:4px solid #64b5f6;}
.section.error{border-color:#f44336;}
h1{color:#fff;}h2{color:#64b5f6;}
pre{background:#111;padding:12px;border-radius:4px;overflow:auto;color:#a5d6a7;white-space:pre-wrap;}
</style></head><body>';

echo '<h1>⚙️ Production Setup Runner</h1>';
echo '<p style="color:#aaa;">Running at: ' . date('Y-m-d H:i:s') . ' UTC</p>';
echo '<p class="warn">⚠️ DELETE this file after setup is complete!</p>';

// Step 1: Check environment
echo '<div class="section"><h2>Step 1: Environment Check</h2>';
echo '<p>PHP: <span class="ok">' . phpversion() . '</span></p>';
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    echo '<p>.env file: <span class="ok">✓ Found</span></p>';
    $env = parse_ini_file($envFile);
    echo '<p>APP_URL: <span class="ok">' . htmlspecialchars($env['APP_URL'] ?? 'not set') . '</span></p>';
    echo '<p>DB_HOST: <span class="ok">' . htmlspecialchars($env['DB_HOST'] ?? 'not set') . '</span></p>';
    echo '<p>DB_DATABASE: <span class="ok">' . htmlspecialchars($env['DB_DATABASE'] ?? 'not set') . '</span></p>';
    echo '<p>DB_USERNAME: <span class="ok">' . htmlspecialchars($env['DB_USERNAME'] ?? 'not set') . '</span></p>';
    echo '<p>DB_PASSWORD: <span class="ok">' . (!empty($env['DB_PASSWORD'] ?? '') ? '[SET]' : '<span class="warn">[EMPTY - may be wrong]</span>') . '</span></p>';
} else {
    echo '<p class="fail">✗ .env file NOT FOUND! You must create it with your hosting credentials.</p>';
    echo '<p>Create a .env file at the application root (one level above public/) and add your database settings.</p>';
}
echo '</div>';

// Step 2: Test DB Connection
echo '<div class="section"><h2>Step 2: Database Connection Test</h2>';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $db   = $env['DB_DATABASE'] ?? '';
    $user = $env['DB_USERNAME'] ?? 'root';
    $pass = $env['DB_PASSWORD'] ?? '';
    try {
        $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8", $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
        echo '<p class="ok">✓ Database connected successfully!</p>';
        // Check tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo '<p>Tables found: <span class="ok">' . count($tables) . '</span></p>';
        if (count($tables) === 0) {
            echo '<p class="warn">⚠ No tables found — migrations have not been run yet.</p>';
        } else {
            echo '<p>Existing tables: ' . implode(', ', array_map('htmlspecialchars', $tables)) . '</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="fail">✗ Database connection FAILED!</p>';
        echo '<p class="fail">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p class="warn">Fix your .env DB_ settings and try again.</p>';
    }
} else {
    echo '<p class="warn">Skipped — .env not found.</p>';
}
echo '</div>';

// Step 3: Storage permissions   
echo '<div class="section"><h2>Step 3: Storage Permissions</h2>';
$paths = [
    __DIR__ . '/../storage',
    __DIR__ . '/../storage/logs',
    __DIR__ . '/../storage/framework',
    __DIR__ . '/../storage/framework/cache',
    __DIR__ . '/../storage/framework/sessions',
    __DIR__ . '/../storage/framework/views',
    __DIR__ . '/../bootstrap/cache',
];
$allOk = true;
foreach ($paths as $path) {
    if (!file_exists($path)) {
        @mkdir($path, 0775, true);
    }
    $writable = is_writable($path);
    if (!$writable) {
        @chmod($path, 0775);
        $writable = is_writable($path);
    }
    $name = str_replace(__DIR__ . '/../', '', $path);
    echo '<p>' . htmlspecialchars($name) . ': ';
    echo $writable ? '<span class="ok">✓ Writable</span>' : '<span class="fail">✗ NOT Writable — set chmod 775 via File Manager</span>';
    echo '</p>';
    if (!$writable) $allOk = false;
}
echo $allOk ? '<p class="ok">✓ All storage directories are writable</p>' : '<p class="warn">⚠ Some directories are not writable. Use cPanel File Manager to set permissions to 755 or 775.</p>';
echo '</div>';

// Step 4: Run Artisan if accessible
echo '<div class="section"><h2>Step 4: Run Artisan Commands</h2>';
$artisan = __DIR__ . '/../artisan';
if (file_exists($artisan) && file_exists(__DIR__ . '/../vendor/autoload.php')) {
    
    $commands = [
        'config:clear'   => 'Clear config cache',
        'cache:clear'    => 'Clear application cache',
        'route:clear'    => 'Clear route cache',
        'view:clear'     => 'Clear view cache',
        'migrate --force'=> 'Run database migrations',
    ];
    
    foreach ($commands as $cmd => $label) {
        echo "<p>Running: <code>php artisan {$cmd}</code> ({$label})...</p>";
        $output = shell_exec("cd " . escapeshellarg(dirname(__DIR__)) . " && php artisan {$cmd} 2>&1");
        if ($output !== null) {
            echo '<pre>' . htmlspecialchars($output) . '</pre>';
        } else {
            // Try exec
            $result = [];
            exec("cd " . escapeshellarg(dirname(__DIR__)) . " && php artisan {$cmd} 2>&1", $result);
            if (!empty($result)) {
                echo '<pre>' . htmlspecialchars(implode("\n", $result)) . '</pre>';
            } else {
                echo '<p class="warn">⚠ shell_exec/exec may be disabled. Run commands manually via SSH or cPanel Terminal.</p>';
                break;
            }
        }
    }
} else {
    echo '<p class="warn">⚠ Artisan or vendor directory not found. Make sure vendor/ folder was uploaded.</p>';
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        echo '<p class="fail">✗ vendor/autoload.php is MISSING — You must upload the vendor/ folder or run <code>composer install --no-dev</code> on the server.</p>';
    }
}
echo '</div>';

// Step 5: Check vendor
echo '<div class="section"><h2>Step 5: Vendor Check</h2>';
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo '<p class="ok">✓ vendor/ directory exists</p>';
} else {
    echo '<p class="fail">✗ vendor/ directory MISSING!</p>';
    echo '<p>You need to either:<br>';
    echo '1. Upload the vendor/ folder from your local machine<br>';
    echo '2. OR run <code>composer install --no-dev --optimize-autoloader</code> via SSH</p>';
}
echo '</div>';

// Step 6: Check built assets
echo '<div class="section"><h2>Step 6: Compiled Assets Check</h2>';
$manifestFile = __DIR__ . '/build/manifest.json';
if (file_exists($manifestFile)) {
    echo '<p class="ok">✓ build/manifest.json found — assets compiled</p>';
} else {
    echo '<p class="fail">✗ build/manifest.json NOT FOUND</p>';
    echo '<p class="warn">You need to either:<br>';
    echo '1. Upload the public/build/ folder from your local machine (run <code>npm run build</code> first)<br>';
    echo '2. OR the build folder was not uploaded</p>';
}
echo '</div>';

// Step 7: Final test
echo '<div class="section"><h2>Step 7: Laravel Bootstrap Test</h2>';
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo '<p class="ok">✓ Laravel bootstraps successfully!</p>';
    echo '<p class="ok">If you see this, your app should work. Try visiting the homepage now.</p>';
} catch (\Throwable $e) {
    echo '<p class="fail">✗ Laravel bootstrap FAILED</p>';
    echo '<p class="fail">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>File: ' . htmlspecialchars($e->getFile()) . ' Line: ' . $e->getLine() . '</p>';
}
echo '</div>';

echo '<div class="section" style="border-color:#f44336;">';
echo '<h2 style="color:#f44336;">🚨 Security Notice</h2>';
echo '<p class="fail"><strong>DELETE this file (run-setup.php) from your server immediately after setup is complete!</strong></p>';
echo '<p>It exposes sensitive configuration information.</p>';
echo '</div>';

echo '</body></html>';
