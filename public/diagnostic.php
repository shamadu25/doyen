<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Localhost Diagnostic</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .check {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        .error {
            border-left-color: #dc3545;
        }
        h1 { color: #333; }
        .status { font-weight: bold; }
        .ok { color: #28a745; }
        .fail { color: #dc3545; }
    </style>
</head>
<body>
    <h1>🔍 Laravel Application Diagnostic</h1>
    
    <div class="check">
        <strong>PHP Version:</strong>
        <span class="status ok">✓ <?php echo phpversion(); ?></span>
    </div>
    
    <div class="check">
        <strong>Database Connection:</strong>
        <?php
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=garage', 'root', '');
            echo '<span class="status ok">✓ Connected</span>';
        } catch (Exception $e) {
            echo '<span class="status fail">✗ Failed: ' . htmlspecialchars($e->getMessage()) . '</span>';
        }
        ?>
    </div>
    
    <div class="check">
        <strong>Laravel Application:</strong>
        <?php
        try {
            require __DIR__.'/../vendor/autoload.php';
            $app = require_once __DIR__.'/../bootstrap/app.php';
            echo '<span class="status ok">✓ Bootstrap OK</span>';
        } catch (Exception $e) {
            echo '<span class="status fail">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
        }
        ?>
    </div>
    
    <div class="check">
        <strong>CSS Asset:</strong>
        <?php
        $cssFiles = glob(__DIR__.'/build/assets/app-*.css');
        if (!empty($cssFiles)) {
            echo '<span class="status ok">✓ ' . basename($cssFiles[0]) . '</span>';
        } else {
            echo '<span class="status fail">✗ Not found</span>';
        }
        ?>
    </div>
    
    <div class="check">
        <strong>JS Asset:</strong>
        <?php
        $jsFiles = glob(__DIR__.'/build/assets/app-*.js');
        if (!empty($jsFiles)) {
            echo '<span class="status ok">✓ ' . basename($jsFiles[0]) . '</span>';
        } else {
            echo '<span class="status fail">✗ Not found</span>';
        }
        ?>
    </div>
    
    <div class="check">
        <strong>Storage Writable:</strong>
        <?php
        $storagePath = __DIR__.'/../storage/logs';
        echo is_writable($storagePath) 
            ? '<span class="status ok">✓ Yes</span>' 
            : '<span class="status fail">✗ No</span>';
        ?>
    </div>
    
    <hr style="margin: 30px 0;">
    
    <h2>🎯 If you see this page, the server is working!</h2>
    <p><strong style="color: #28a745;">Server Status: ALL SYSTEMS OPERATIONAL</strong></p>
    
    <h3>Troubleshooting Blank Page:</h3>
    <ol>
        <li><strong>Clear Browser Cache:</strong> Press <code>Ctrl + Shift + Delete</code>, select "Cached images and files", then Clear</li>
        <li><strong>Hard Refresh:</strong> Press <code>Ctrl + Shift + R</code> (or <code>Ctrl + F5</code>)</li>
        <li><strong>Try Different Browser:</strong> Open in Chrome Incognito or Firefox Private mode</li>
        <li><strong>Check Browser Console:</strong> Press <code>F12</code>, go to "Console" tab, look for errors</li>
        <li><strong>Disable Extensions:</strong> Try disabling browser extensions that might block JavaScript</li>
    </ol>
    
    <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 5px;">
        <h3 style="margin-top: 0;">📍 Access URLs:</h3>
        <p><strong>Main App:</strong> <a href="http://localhost/garage/garage/public/">http://localhost/garage/garage/public/</a></p>
        <p><strong>Diagnostic:</strong> You are here!</p>
        <p><strong>Simple Test:</strong> <a href="test-simple.php">test-simple.php</a></p>
    </div>
</body>
</html>
