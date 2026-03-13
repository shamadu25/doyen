# 🔍 MOT Testing Integration - UK DVLA/DVSA API

## Overview

Full integration with UK government APIs for MOT (Ministry of Transport) testing:
- **DVLA API** - Vehicle details and MOT expiry dates
- **DVSA API** - Complete MOT test history and results

---

## 🎯 Features

### 1. Automatic MOT History Retrieval
- Fetch complete MOT history from DVSA database
- Import test results, advisories, and failures
- Automatic duplicate detection
- Updates vehicle MOT expiry date

### 2. MOT Test Management
- Record manual MOT tests
- View test history per vehicle
- Track advisories and failures
- Link MOT tests to job cards

### 3. MOT Due Alerts
- Dashboard widget showing vehicles due for MOT
- Configurable reminder periods (default 30 days)
- Automatic expiry date tracking

### 4. Real-time MOT Status Check
- Check current MOT validity
- Calculate days until expiry
- View latest test result

---

## 📡 API Integration

### DVLA API (Vehicle Details)
**Endpoint**: `https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles`

**Returns**:
- Registration number
- Make, model, color
- Engine capacity
- Fuel type
- Year of manufacture
- **MOT expiry date**
- Tax due date

### DVSA API (MOT History)
**Endpoint**: `https://beta.check-mot.service.gov.uk/trade/vehicles/mot-tests`  
**Authentication**: OAuth2 (Microsoft Azure AD) + API Key

**OAuth2 Flow**:
1. Request access token from Azure AD token endpoint
2. Include token in `Authorization: Bearer` header
3. Include API key in `x-api-key` header
4. Token cached for 55 minutes

**Required Credentials**:
- Client ID
- Client Secret
- API Key
- Scope URL: `https://tapi.dvsa.gov.uk/.default`
- Token URL: Microsoft Azure AD tenant-specific

**Returns**:
- Complete MOT test history
- Test numbers and dates
- Pass/Fail results
- Mileage at test
- Advisories (warnings)
- Failures (defects)
- Dangerous defects

---

## 🔧 Configuration

### Environment Variables

Add to `.env`:

```env
# DVLA API (Vehicle Details)
DVLA_API_KEY=your_dvla_api_key_here

# DVSA API (MOT History) - OAuth2 Credentials
DVSA_CLIENT_ID=your-dvsa-client-id
DVSA_CLIENT_SECRET=your-dvsa-client-secret
DVSA_API_KEY=your-dvsa-api-key
DVSA_SCOPE=https://tapi.dvsa.gov.uk/.default
DVSA_TOKEN_URL=https://login.microsoftonline.com/your-tenant-id/oauth2/v2.0/token
DVSA_API_URL=https://beta.check-mot.service.gov.uk/trade/vehicles/mot-tests
```

### Get API Keys

1. **DVLA API Key**:
   - Visit: https://developer-portal.driver-vehicle-licensing.api.gov.uk/
   - Register for an account
   - Subscribe to "Vehicle Enquiry Service"
   - Copy API key to `.env`

2. **DVSA API Credentials** (OAuth2):
   - Visit: https://documentation.history.mot.api.gov.uk/
   - Apply for trade access
   - Receive credentials via email:
     - Client ID
     - Client Secret
     - API Key
     - Scope URL
     - Token URL
   - Copy all credentials to `.env`

**Important**: DVSA uses OAuth2 authentication. The system automatically:
- Requests access tokens from Microsoft Azure AD
- Caches tokens for 55 minutes
- Refreshes tokens automatically when expired

### Services Configuration

File: `config/services.php`

```php
'dvla' => [
    'api_key' => env('DVLA_API_KEY'),
    'base_url' => 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1',
],

'dvsa' => [
    'api_key' => env('DVSA_API_KEY'),
    'base_url' => 'https://beta.check-mot.service.gov.uk/trade/vehicles/mot-tests',
],
```

---

## 🚀 Usage

### 1. Fetch MOT History for a Vehicle

**From Admin Panel**:
1. Navigate to vehicle details
2. Click "Fetch MOT History"
3. System imports all historical tests from DVSA
4. Updates vehicle MOT expiry date

**Programmatically**:
```php
use App\Services\DvsaService;

$dvsaService = new DvsaService();
$motHistory = $dvsaService->getMotHistory('AB12CDE');

// Get latest test
$latestTest = $dvsaService->getLatestMotTest($motHistory);

// Check if MOT is valid
$hasValidMot = $dvsaService->hasValidMot($motHistory);
```

### 2. Record a New MOT Test

**Manual Entry**:
1. Go to MOT Tests → Create New
2. Select vehicle or job card
3. Enter test details:
   - Test date and expiry
   - Mileage
   - Result (Pass/Fail)
   - Advisories
   - Failures
4. System automatically updates vehicle MOT due date if passed

**From Job Card**:
1. Open job card for MOT test
2. Click "Record MOT Test"
3. Pre-filled with vehicle and job card details
4. Complete test information

### 3. Check MOT Status

**API Endpoint**:
```javascript
GET /mot-tests/vehicle/{vehicle}/check-status

// Response
{
    "success": true,
    "data": {
        "has_valid_mot": true,
        "expiry_date": "2026-05-15",
        "test_result": "PASSED",
        "mileage": 45000,
        "days_until_expiry": 108
    }
}
```

**In Code**:
```php
$status = $motTestController->checkStatus($vehicle);
```

### 4. View Vehicles Due for MOT

**Dashboard Widget**:
- Shows vehicles with MOT expiring in next 30 days
- Click to view full list

**Full List**:
```
GET /mot-tests-due-soon?days=30
```

---

## 📊 Database Schema

### `mot_tests` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `vehicle_id` | bigint | Foreign key to vehicles |
| `job_card_id` | bigint | Optional link to job card |
| `test_number` | string | DVSA test number |
| `test_date` | datetime | When test was performed |
| `expiry_date` | date | MOT certificate expiry |
| `mileage` | integer | Odometer reading at test |
| `test_result` | enum | passed/failed |
| `test_class` | string | Vehicle class (4, 5, 7, etc.) |
| `advisories` | json | Array of advisory items |
| `failures` | json | Array of failure items |
| `dvsa_data` | json | Complete DVSA API response |
| `notes` | text | Additional notes |
| `created_at` | timestamp | Record created |
| `updated_at` | timestamp | Record updated |
| `deleted_at` | timestamp | Soft delete |

### Advisories/Failures JSON Structure

```json
{
    "advisories": [
        {
            "text": "Offside Front Tyre has a cut in excess of the requirements deep enough to reach the ply or cords",
            "dangerous": false
        }
    ],
    "failures": [
        {
            "text": "Nearside Front Brake pad(s) less than 1.5 mm thick",
            "type": "MAJOR",
            "dangerous": true
        }
    ]
}
```

---

## 🎨 Routes

| Method | Route | Action | Description |
|--------|-------|--------|-------------|
| GET | `/mot-tests` | index | List all MOT tests |
| GET | `/mot-tests/create` | create | Show create form |
| POST | `/mot-tests` | store | Save new test |
| GET | `/mot-tests/{id}` | show | View test details |
| GET | `/mot-tests/{id}/edit` | edit | Show edit form |
| PUT | `/mot-tests/{id}` | update | Update test |
| DELETE | `/mot-tests/{id}` | destroy | Delete test |
| POST | `/mot-tests/vehicle/{vehicle}/fetch-history` | fetchHistory | Import from DVSA |
| GET | `/mot-tests/vehicle/{vehicle}/history` | getHistory | Get API data |
| GET | `/mot-tests/vehicle/{vehicle}/check-status` | checkStatus | Check MOT status |
| GET | `/mot-tests-due-soon` | dueSoon | Vehicles due for MOT |
| POST | `/mot-tests/vehicle/{vehicle}/refresh-data` | refreshVehicleData | Update from DVLA |

---

## 💡 Controller Methods

### MotTestController

```php
// Standard CRUD
index()         // List all tests
create()        // Show create form
store()         // Save new test
show()          // View test details
edit()          // Show edit form
update()        // Update test
destroy()       // Delete test

// API Integration
fetchHistory($vehicle)          // Import from DVSA
getHistory($vehicle)            // Get raw API data
checkStatus($vehicle)           // Check current MOT status
refreshVehicleData($vehicle)    // Update from DVLA

// Reports
dueSoon($request)              // Vehicles due for MOT
```

---

## 🔐 Services

### DvsaService

**Location**: `app/Services/DvsaService.php`

**Methods**:

```php
// Get complete MOT history
getMotHistory(string $registration): ?array

// Get latest test from history
getLatestMotTest(array $motHistory): ?array

// Parse advisories and failures
parseMotDefects(array $motTest): array

// Format for database storage
formatMotTestData(array $motTest): array

// Check if MOT is valid
hasValidMot(array $motHistory): bool
```

### DvlaService

**Location**: `app/Services/DvlaService.php`

**Methods**:

```php
// Get vehicle details (includes MOT expiry)
getVehicleDetails(string $registration): ?array

// Format DVLA data for vehicle record
formatVehicleData(array $dvlaData): array

// Check if MOT due soon
isMotDueSoon(string $motExpiryDate, int $daysThreshold = 30): bool

// Check if tax due soon
isTaxDueSoon(string $taxDueDate, int $daysThreshold = 14): bool
```

---

## 📈 Dashboard Integration

### Widgets

**MOT Due Soon**:
```php
// In DashboardController
$motDueSoon = Vehicle::whereNotNull('mot_due_date')
    ->where('mot_due_date', '<=', Carbon::now()->addDays(30))
    ->where('mot_due_date', '>=', Carbon::now())
    ->count();
```

**MOT Tests This Month**:
```php
$motTestsThisMonth = MotTest::whereBetween('test_date', [
    Carbon::now()->startOfMonth(),
    Carbon::now()->endOfMonth()
])->count();
```

**MOT Pass Rate**:
```php
$passRate = MotTest::where('test_date', '>=', Carbon::now()->subMonths(3))
    ->selectRaw('COUNT(*) as total, SUM(CASE WHEN test_result = "passed" THEN 1 ELSE 0 END) as passed')
    ->first();

$passPercentage = ($passRate->passed / $passRate->total) * 100;
```

---

## 🔔 Notifications

### Email Reminders

Send MOT reminder emails 30 days before expiry:

```php
use App\Mail\MotReminderMail;

$vehiclesDue = Vehicle::whereNotNull('mot_due_date')
    ->where('mot_due_date', Carbon::now()->addDays(30))
    ->with('customer')
    ->get();

foreach ($vehiclesDue as $vehicle) {
    Mail::to($vehicle->customer->email)
        ->send(new MotReminderMail($vehicle));
}
```

### SMS Reminders

Send SMS 7 days before expiry:

```php
use App\Services\SmsService;

$smsService = new SmsService();

foreach ($vehiclesDue as $vehicle) {
    $message = "MOT Reminder: Your {$vehicle->make} {$vehicle->model} ({$vehicle->registration_number}) MOT expires on {$vehicle->mot_due_date->format('d/m/Y')}. Book now at DOYEN AUTO.";
    
    $smsService->send($vehicle->customer->phone, $message);
}
```

---

## 🧪 Testing

### Test MOT History Fetch

```bash
php artisan tinker

$vehicle = Vehicle::where('registration_number', 'AB12CDE')->first();
$controller = new App\Http\Controllers\MotTestController(
    new App\Services\DvsaService(),
    new App\Services\DvlaService()
);
$result = $controller->fetchHistory($vehicle);
```

### Check API Connectivity

```php
// Test DVSA
$dvsa = new App\Services\DvsaService();
$history = $dvsa->getMotHistory('AB12CDE');
dd($history);

// Test DVLA
$dvla = new App\Services\DvlaService();
$details = $dvla->getVehicleDetails('AB12CDE');
dd($details);
```

---

## 📋 Workflow Example

### Complete MOT Test Workflow

1. **Customer books MOT appointment**
   - Appointment created with type "MOT"
   - Scheduled date set

2. **Receptionist confirms booking**
   - Appointment status: confirmed
   - Customer receives confirmation email/SMS

3. **Mechanic performs MOT test**
   - Create job card from appointment
   - Add MOT test service
   - Perform inspection

4. **Record MOT results**
   - Go to MOT Tests → Create New
   - Link to job card
   - Enter:
     - Test result (Pass/Fail)
     - Mileage
     - Advisories (e.g., "Front tyres worn")
     - Failures (e.g., "Brake pads below minimum")

5. **If Failed - Create Repair Job Card**
   - Convert failures to repair items
   - Add required parts
   - Schedule repair appointment

6. **If Passed - Complete Job**
   - System updates vehicle MOT expiry (+12 months)
   - Create invoice for MOT test fee
   - Issue MOT certificate

7. **Automatic Follow-up**
   - 11 months later: Email reminder sent
   - 23 days before expiry: SMS reminder sent
   - Dashboard shows in "MOT Due Soon" widget

---

## 🎯 Business Benefits

### For Garage

1. **Increased MOT bookings** - Automated reminders bring customers back
2. **Better planning** - Know which vehicles are due
3. **Complete history** - Full DVSA data for every vehicle
4. **Compliance** - Proper record keeping
5. **Upsell opportunities** - Convert advisories to services

### For Customers

1. **Never miss MOT expiry** - Automatic reminders
2. **Complete history** - View all past tests
3. **Transparency** - See advisories and failures
4. **Convenience** - Book online when reminded
5. **Trust** - Official DVSA data

---

## 🚨 Error Handling

### API Failures

```php
try {
    $motHistory = $dvsaService->getMotHistory($registration);
    
    if (!$motHistory) {
        Log::warning("No MOT history found for {$registration}");
        return back()->with('warning', 'No MOT history available');
    }
    
} catch (\Exception $e) {
    Log::error("DVSA API error: " . $e->getMessage());
    return back()->with('error', 'Unable to connect to DVSA service');
}
```

### Rate Limiting

DVSA API has rate limits:
- **60 requests per minute**
- **10,000 requests per day**

Implement caching:

```php
use Illuminate\Support\Facades\Cache;

$cacheKey = "mot_history_{$registration}";

$motHistory = Cache::remember($cacheKey, 3600, function() use ($dvsaService, $registration) {
    return $dvsaService->getMotHistory($registration);
});
```

---

## 📊 Reports

### MOT Performance Report

```php
// Tests by result
MotTest::selectRaw('test_result, COUNT(*) as count')
    ->whereBetween('test_date', [$startDate, $endDate])
    ->groupBy('test_result')
    ->get();

// Tests by month
MotTest::selectRaw('MONTH(test_date) as month, COUNT(*) as count')
    ->whereYear('test_date', Carbon::now()->year)
    ->groupBy('month')
    ->get();

// Common advisories
MotTest::selectRaw('advisories')
    ->whereNotNull('advisories')
    ->get()
    ->pluck('advisories')
    ->flatten()
    ->groupBy('text')
    ->map(fn($items) => $items->count())
    ->sortDesc()
    ->take(10);
```

---

## 🔄 Scheduled Tasks

### Daily MOT Reminders

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Send MOT reminders at 9 AM daily
    $schedule->call(function () {
        $vehicles = Vehicle::whereNotNull('mot_due_date')
            ->where('mot_due_date', Carbon::now()->addDays(30))
            ->with('customer')
            ->get();
            
        foreach ($vehicles as $vehicle) {
            Mail::to($vehicle->customer->email)
                ->queue(new MotReminderMail($vehicle));
        }
    })->dailyAt('09:00');
}
```

---

## 🎉 Summary

Your garage now has **full MOT testing integration** with UK government APIs:

✅ Automatic MOT history retrieval from DVSA  
✅ Real-time MOT status checking  
✅ MOT due date tracking and reminders  
✅ Complete test result management  
✅ Advisories and failures tracking  
✅ Integration with job cards and invoicing  
✅ Dashboard widgets for MOT due alerts  
✅ API endpoints for external integrations  

**Next Steps**:
1. Obtain API keys from DVLA and DVSA
2. Configure `.env` with API keys
3. Test API connectivity
4. Create MOT test views (optional - can be done separately)
5. Set up automated reminders
6. Train staff on MOT workflow

---

## 📞 Support

For API issues:
- **DVLA**: https://developer-portal.driver-vehicle-licensing.api.gov.uk/
- **DVSA**: https://documentation.history.mot.api.gov.uk/

For system issues:
- Review logs: `storage/logs/laravel.log`
- Check API connectivity with test scripts above
