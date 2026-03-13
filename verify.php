<!DOCTYPE html>
<html>
<head>
    <title>Garage System - Setup Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2563eb;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 10px;
        }
        .status {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 5px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        ul {
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚗 Garage Management System - Setup Check</h1>
        
        <?php
        // Check PHP version
        $phpVersion = phpversion();
        $phpOk = version_compare($phpVersion, '8.1', '>=');
        ?>
        
        <div class="status <?php echo $phpOk ? 'success' : 'error'; ?>">
            ✓ PHP Version: <?php echo $phpVersion; ?> <?php echo $phpOk ? '(OK)' : '(Requires 8.1+)'; ?>
        </div>
        
        <?php
        // Check if Laravel files exist
        $laravelExists = file_exists(__DIR__ . '/vendor/autoload.php');
        ?>
        
        <div class="status <?php echo $laravelExists ? 'success' : 'error'; ?>">
            <?php echo $laravelExists ? '✓' : '✗'; ?> Laravel Installation: <?php echo $laravelExists ? 'Found' : 'Not Found'; ?>
        </div>
        
        <?php
        // Check if .env exists
        $envExists = file_exists(__DIR__ . '/.env');
        ?>
        
        <div class="status <?php echo $envExists ? 'success' : 'error'; ?>">
            <?php echo $envExists ? '✓' : '✗'; ?> Environment File (.env): <?php echo $envExists ? 'Found' : 'Missing'; ?>
        </div>
        
        <div class="status info">
            📍 Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?><br>
            📍 Current Path: <?php echo __DIR__; ?><br>
            📍 Request URI: <?php echo $_SERVER['REQUEST_URI']; ?>
        </div>
        
        <h2>Access Your Application:</h2>
        <div style="margin: 20px 0;">
            <a href="/garage/public/" class="btn">🚀 Open Garage System</a>
            <a href="/garage/public/login" class="btn">🔐 Admin Login</a>
            <a href="/garage/public/portal/login" class="btn">👤 Customer Portal</a>
        </div>
        
        <h2>Quick Setup Steps:</h2>
        <ul>
            <li>✅ Apache is running on port 80</li>
            <li>✅ PHP <?php echo $phpVersion; ?> is active</li>
            <?php if (!$laravelExists): ?>
                <li class="error">❌ Run: <code>composer install</code></li>
            <?php endif; ?>
            <?php if (!$envExists): ?>
                <li class="error">❌ Copy .env.example to .env</li>
                <li class="error">❌ Run: <code>php artisan key:generate</code></li>
            <?php endif; ?>
            <li>Start MySQL in XAMPP Control Panel</li>
            <li>Run: <code>php artisan migrate</code></li>
            <li>Access: <code>http://localhost/garage/public/</code></li>
        </ul>
        
        <h2>Alternative URLs:</h2>
        <ul>
            <li><strong>Main:</strong> <a href="http://localhost/garage/public/">http://localhost/garage/public/</a></li>
            <li><strong>Auto-redirect:</strong> <a href="http://localhost/garage/">http://localhost/garage/</a></li>
        </ul>
    </div>
</body>
</html>
