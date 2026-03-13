<?php
/**
 * API Configuration Status Report
 * Shows which APIs are configured and which need setup
 */

// Load environment variables
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("❌ .env file not found\n");
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];

foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

function isConfigured($value) {
    if (empty($value)) return false;
    if (in_array($value, ['your_', 'null', 'false', ''])) return false;
    if (strpos($value, 'your_') === 0) return false;
    if (strpos($value, 'example') !== false) return false;
    return true;
}

function checkApiStatus($env, $keys, $name) {
    $allSet = true;
    foreach ($keys as $key) {
        if (!isset($env[$key]) || !isConfigured($env[$key])) {
            $allSet = false;
            break;
        }
    }
    return $allSet;
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         API CONFIGURATION STATUS REPORT                      ║\n";
echo "║         Doyen Auto Services - Garage Management System       ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Core APIs
echo "🔐 CORE APIs (Vehicle & MOT)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$apis = [
    'DVSA MOT API' => [
        'keys' => ['DVSA_CLIENT_ID', 'DVSA_CLIENT_SECRET', 'DVSA_API_KEY'],
        'priority' => 'MANDATORY',
        'purpose' => 'Retrieve MOT history, test results, advisories',
        'cost' => 'FREE',
    ],
    'DVLA Vehicle API' => [
        'keys' => ['DVLA_API_KEY'],
        'priority' => 'RECOMMENDED',
        'purpose' => 'Get vehicle make/model/color by registration',
        'cost' => '£0.10 per lookup',
    ],
];

foreach ($apis as $name => $info) {
    $status = checkApiStatus($env, $info['keys'], $name);
    $icon = $status ? '✅' : '❌';
    $statusText = $status ? 'CONFIGURED' : 'NOT CONFIGURED';
    
    echo "\n{$icon} {$name}\n";
    echo "   Status: {$statusText}\n";
    echo "   Priority: {$info['priority']}\n";
    echo "   Purpose: {$info['purpose']}\n";
    echo "   Cost: {$info['cost']}\n";
}

// Communication APIs
echo "\n\n💬 COMMUNICATION APIs\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$commApis = [
    'Email (SMTP)' => [
        'keys' => ['MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD'],
        'priority' => 'MANDATORY',
        'purpose' => 'Send invoices, quotes, appointment confirmations',
        'cost' => 'FREE (Gmail/Outlook)',
    ],
    'SMS (Twilio)' => [
        'keys' => ['TWILIO_SID', 'TWILIO_TOKEN'],
        'priority' => 'OPTIONAL',
        'purpose' => 'SMS reminders for appointments and MOT',
        'cost' => '~£0.04 per SMS',
    ],
    'WhatsApp' => [
        'keys' => ['WHATSAPP_FROM'],
        'priority' => 'OPTIONAL',
        'purpose' => 'WhatsApp notifications via Twilio',
        'cost' => '~£0.005 per message',
    ],
    'Tawk.to Live Chat' => [
        'keys' => ['TAWK_PROPERTY_ID', 'TAWK_WIDGET_ID'],
        'priority' => 'OPTIONAL',
        'purpose' => 'Live chat widget for website',
        'cost' => 'FREE',
    ],
];

foreach ($commApis as $name => $info) {
    $status = checkApiStatus($env, $info['keys'], $name);
    $icon = $status ? '✅' : '❌';
    $statusText = $status ? 'CONFIGURED' : 'NOT CONFIGURED';
    
    echo "\n{$icon} {$name}\n";
    echo "   Status: {$statusText}\n";
    echo "   Priority: {$info['priority']}\n";
    echo "   Purpose: {$info['purpose']}\n";
    echo "   Cost: {$info['cost']}\n";
}

// Payment APIs
echo "\n\n💳 PAYMENT PROCESSING\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$paymentApis = [
    'Stripe' => [
        'keys' => ['STRIPE_KEY', 'STRIPE_SECRET'],
        'priority' => 'RECOMMENDED',
        'purpose' => 'Online card payments, deposits, invoices',
        'cost' => '1.4% + 20p per transaction',
    ],
];

foreach ($paymentApis as $name => $info) {
    $status = checkApiStatus($env, $info['keys'], $name);
    $icon = $status ? '✅' : '❌';
    $statusText = $status ? 'CONFIGURED' : 'NOT CONFIGURED';
    
    echo "\n{$icon} {$name}\n";
    echo "   Status: {$statusText}\n";
    echo "   Priority: {$info['priority']}\n";
    echo "   Purpose: {$info['purpose']}\n";
    echo "   Cost: {$info['cost']}\n";
}

// Parts Supplier APIs
echo "\n\n🔧 PARTS SUPPLIER APIs\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$partsApis = [
    'TecDoc' => [
        'keys' => ['TECDOC_API_KEY', 'TECDOC_PROVIDER_ID'],
        'priority' => 'OPTIONAL',
        'purpose' => 'Comprehensive parts catalog and cross-reference',
        'cost' => 'Contact TecDoc',
    ],
    'Euro Car Parts' => [
        'keys' => ['EUROCARPARTS_API_KEY'],
        'priority' => 'OPTIONAL',
        'purpose' => 'Parts pricing and availability',
        'cost' => 'Contact ECP',
    ],
    'GSF Car Parts' => [
        'keys' => ['GSF_API_KEY'],
        'priority' => 'OPTIONAL',
        'purpose' => 'Parts pricing and availability',
        'cost' => 'Contact GSF',
    ],
    'AutoDoc' => [
        'keys' => ['AUTODOC_API_KEY'],
        'priority' => 'OPTIONAL',
        'purpose' => 'European parts supplier',
        'cost' => 'Contact AutoDoc',
    ],
    'Oscaro' => [
        'keys' => ['OSCARO_API_KEY'],
        'priority' => 'OPTIONAL',
        'purpose' => 'Online parts marketplace',
        'cost' => 'Contact Oscaro',
    ],
];

foreach ($partsApis as $name => $info) {
    $status = checkApiStatus($env, $info['keys'], $name);
    $icon = $status ? '✅' : '❌';
    $statusText = $status ? 'CONFIGURED' : 'NOT CONFIGURED';
    
    echo "\n{$icon} {$name}\n";
    echo "   Status: {$statusText}\n";
    echo "   Priority: {$info['priority']}\n";
    echo "   Purpose: {$info['purpose']}\n";
    echo "   Cost: {$info['cost']}\n";
}

// Business Integration
echo "\n\n🏢 BUSINESS INTEGRATIONS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$businessApis = [
    'Google Business Profile' => [
        'keys' => ['GOOGLE_PLACE_ID', 'GOOGLE_REVIEW_LINK'],
        'priority' => 'RECOMMENDED',
        'purpose' => 'Customer reviews integration',
        'cost' => 'FREE',
    ],
];

foreach ($businessApis as $name => $info) {
    $status = checkApiStatus($env, $info['keys'], $name);
    $icon = $status ? '✅' : '❌';
    $statusText = $status ? 'CONFIGURED' : 'NOT CONFIGURED';
    
    echo "\n{$icon} {$name}\n";
    echo "   Status: {$statusText}\n";
    echo "   Priority: {$info['priority']}\n";
    echo "   Purpose: {$info['purpose']}\n";
    echo "   Cost: {$info['cost']}\n";
}

// Summary
echo "\n\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                        SUMMARY                               ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$configured = 0;
$total = 0;

foreach (array_merge($apis, $commApis, $paymentApis, $partsApis, $businessApis) as $name => $info) {
    $total++;
    if (checkApiStatus($env, $info['keys'], $name)) {
        $configured++;
    }
}

echo "✅ Configured: {$configured}/{$total} APIs\n";
echo "❌ Not Configured: " . ($total - $configured) . "/{$total} APIs\n\n";

echo "📊 Configuration Progress: " . round(($configured / $total) * 100) . "%\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📝 NEXT STEPS:\n\n";

// Recommend next actions
$recommendations = [];

if (!checkApiStatus($env, ['MAIL_HOST', 'MAIL_USERNAME'], 'Email')) {
    $recommendations[] = "1️⃣  URGENT: Configure email service (required for invoices/quotes)";
}

if (!checkApiStatus($env, ['STRIPE_KEY', 'STRIPE_SECRET'], 'Stripe')) {
    $recommendations[] = "2️⃣  RECOMMENDED: Set up Stripe for online payments";
}

if (!checkApiStatus($env, ['TWILIO_SID'], 'SMS')) {
    $recommendations[] = "3️⃣  OPTIONAL: Enable SMS reminders via Twilio";
}

if (!checkApiStatus($env, ['TAWK_PROPERTY_ID'], 'Tawk')) {
    $recommendations[] = "4️⃣  OPTIONAL: Add live chat with Tawk.to";
}

if (!checkApiStatus($env, ['TECDOC_API_KEY'], 'TecDoc')) {
    $recommendations[] = "5️⃣  OPTIONAL: Integrate parts suppliers for pricing automation";
}

if (empty($recommendations)) {
    echo "🎉 All critical APIs are configured! System is ready.\n";
} else {
    foreach ($recommendations as $rec) {
        echo "{$rec}\n";
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📖 For detailed setup guides, see:\n";
echo "   • API_KEYS_DEPLOYMENT_SUMMARY.md\n";
echo "   • DVLA_API_REGISTRATION_GUIDE.md\n";
echo "   • PARTS_SUPPLIER_GUIDE.md\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
