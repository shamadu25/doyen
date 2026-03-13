<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

echo "==============================================\n";
echo "  EMAIL CONFIGURATION TEST\n";
echo "==============================================\n\n";

// Display current configuration
echo "Current Mail Settings:\n";
echo "  Mailer:      " . Config::get('mail.default') . "\n";
echo "  Host:        " . Config::get('mail.mailers.smtp.host') . "\n";
echo "  Port:        " . Config::get('mail.mailers.smtp.port') . "\n";
echo "  Encryption:  " . Config::get('mail.mailers.smtp.encryption') . "\n";
echo "  Username:    " . Config::get('mail.mailers.smtp.username') . "\n";
echo "  From:        " . Config::get('mail.from.address') . "\n";
echo "  From Name:   " . Config::get('mail.from.name') . "\n";
echo "\n";

// Test email sending
echo "Testing email connection...\n";

try {
    // Send test email
    Mail::raw('This is a test email from Doyen Auto Services Garage Management System.

The email system is configured and working correctly!

Configuration:
- SMTP Host: ' . Config::get('mail.mailers.smtp.host') . '
- Port: ' . Config::get('mail.mailers.smtp.port') . '
- Encryption: ' . Config::get('mail.mailers.smtp.encryption') . '

Time: ' . now()->format('Y-m-d H:i:s') . '
', function($message) {
        $message->to(Config::get('mail.from.address'))
                ->subject('Doyen Auto - Email Test Successful');
    });
    
    echo "✓ Test email sent successfully!\n";
    echo "✓ Check inbox: " . Config::get('mail.from.address') . "\n\n";
    
    echo "==============================================\n";
    echo "  EMAIL SYSTEM: ✓ WORKING\n";
    echo "==============================================\n";
    
} catch (\Exception $e) {
    echo "✗ Email sending failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "==============================================\n";
    echo "  EMAIL SYSTEM: ✗ NOT WORKING\n";
    echo "==============================================\n";
    echo "\nPlease check:\n";
    echo "1. SMTP credentials in .env file\n";
    echo "2. SMTP server is accessible\n";
    echo "3. Firewall/ports are open\n";
    echo "4. Username/password are correct\n";
}
