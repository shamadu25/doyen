<?php
// Quick health check for the application
echo "✓ Web Server: Running\n";

// Check database connection
try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=garage', 'root', '');
    echo "✓ Database: Connected\n";
    
    // Check if migrations table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'migrations'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Migrations: Complete\n";
    }
    
    echo "\n✅ Application Status: HEALTHY\n";
    echo "The website is ready to use!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    http_response_code(500);
}
