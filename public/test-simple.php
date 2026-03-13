<?php
// Simple test to verify PHP is working
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head><title>PHP Test</title></head>";
echo "<body>";
echo "<h1>PHP is Working!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";

// Test database
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=garage', 'root', '');
    echo "<p style='color:green;'>✓ Database Connected</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Database Error: " . $e->getMessage() . "</p>";
}

echo "</body>";
echo "</html>";
