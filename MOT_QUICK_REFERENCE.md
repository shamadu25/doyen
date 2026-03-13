# 🔍 MOT Integration - Quick Reference Card

## 🚀 Quick Commands

```bash
# Send MOT Reminders
php artisan mot:send-reminders              # 30-day email reminders
php artisan mot:send-reminders --days=7     # 7-day reminders
php artisan mot:send-reminders --days=7 --sms  # Email + SMS

# Sync MOT Data
php artisan mot:sync --all                  # Sync all vehicles (limit 10)
php artisan mot:sync --vehicle=123          # Sync specific vehicle ID
php artisan mot:sync --registration=AB12CDE # Sync by registration
php artisan mot:sync --all --limit=50       # Sync up to 50 vehicles

# Test API Connectivity
php artisan tinker
$dvla = new App\Services\DvlaService();
$dvla->getVehicleDetails('AB12CDE');

$dvsa = new App\Services\DvsaService();
$dvsa->getMotHistory('AB12CDE');
```

---

## 📡 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/mot-tests` | List all MOT tests |
| POST | `/mot-tests` | Create new test |
| GET | `/mot-tests/{id}` | View test details |
| PUT | `/mot-tests/{id}` | Update test |
| DELETE | `/mot-tests/{id}` | Delete test |
| POST | `/mot-tests/vehicle/{id}/fetch-history` | Import from DVSA |
| GET | `/mot-tests/vehicle/{id}/check-status` | Check MOT status |
| GET | `/mot-tests-due-soon` | Vehicles due for MOT |

---

## 🔧 Environment Variables

```env
# Add to .env file
DVLA_API_KEY=your_dvla_api_key_here

# DVSA OAuth2 Credentials
DVSA_CLIENT_ID=your-dvsa-client-id
DVSA_CLIENT_SECRET=your-dvsa-client-secret
DVSA_API_KEY=your-dvsa-api-key
DVSA_SCOPE=https://tapi.dvsa.gov.uk/.default
DVSA_TOKEN_URL=https://login.microsoftonline.com/your-tenant-id/oauth2/v2.0/token
```

**Get API Keys**:
- DVLA: https://developer-portal.driver-vehicle-licensing.api.gov.uk/
- DVSA: Already configured (OAuth2 approved) ✅

---

## 📊 Code Snippets

### Fetch MOT History
```php
use App\Services\DvsaService;

$dvsaService = new DvsaService();
$history = $dvsaService->getMotHistory('AB12CDE');
$latestTest = $dvsaService->getLatestMotTest($history);
$isValid = $dvsaService->hasValidMot($history);
```

### Create MOT Test
```php
use App\Models\MotTest;

MotTest::create([
    'vehicle_id' => $vehicleId,
    'test_date' => now(),
    'expiry_date' => now()->addYear(),
    'mileage' => 45000,
    'test_result' => 'passed',
    'advisories' => [['text' => 'Tyres worn', 'dangerous' => false]],
]);
```

### Get Vehicles Due for MOT
```php
use App\Models\Vehicle;
use Carbon\Carbon;

$due = Vehicle::whereNotNull('mot_due_date')
    ->where('mot_due_date', '<=', Carbon::now()->addDays(30))
    ->where('mot_due_date', '>=', Carbon::now())
    ->get();
```

### Check MOT Status (AJAX)
```javascript
fetch('/mot-tests/vehicle/123/check-status')
    .then(r => r.json())
    .then(data => {
        console.log('Valid:', data.data.has_valid_mot);
        console.log('Expires:', data.data.expiry_date);
    });
```

---

## 🔔 Scheduled Tasks

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // MOT reminders at 9 AM daily
    $schedule->command('mot:send-reminders --days=30')
        ->dailyAt('09:00');
    
    // Urgent reminders (7 days) with SMS
    $schedule->command('mot:send-reminders --days=7 --sms')
        ->dailyAt('09:00');
    
    // Weekly data sync (Sundays 2 AM)
    $schedule->command('mot:sync --all --limit=50')
        ->weekly()->sundays()->at('02:00');
}
```

**Enable Scheduler**:
```bash
# Linux/Mac crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

# Windows Task Scheduler
# Command: php artisan schedule:run
# Trigger: Every 1 minute
```

---

## 📁 Key Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/MotTestController.php` | Main controller |
| `app/Services/DvsaService.php` | MOT history API |
| `app/Services/DvlaService.php` | Vehicle details API |
| `app/Models/MotTest.php` | MOT test model |
| `app/Policies/MotTestPolicy.php` | Authorization |
| `app/Console/Commands/SendMotReminders.php` | Reminder command |
| `app/Console/Commands/SyncMotData.php` | Sync command |
| `app/Mail/MotReminderMail.php` | Reminder email |
| `routes/web.php` | MOT routes |
| `MOT_INTEGRATION_GUIDE.md` | Full documentation |

---

## 🎯 Workflow

1. **Customer vehicle registered** → DVLA lookup pulls MOT expiry
2. **System monitors expiry dates** → Dashboard shows due soon
3. **30 days before** → Automated email reminder sent
4. **7 days before** → Email + SMS reminder (urgent)
5. **Customer books MOT** → Appointment created
6. **MOT test performed** → Results recorded in system
7. **If passed** → Vehicle expiry updated (+12 months)
8. **If failed** → Repair job card created with failures
9. **History synced** → Weekly DVSA data import

---

## ⚡ Quick Troubleshooting

**API not working?**
```bash
# Test connectivity with new command
php artisan mot:test-apis
php artisan mot:test-apis AB12CDE

# Check API credentials configured
php artisan tinker
config('services.dvla.api_key')  // Should show your DVLA key
config('services.dvsa.client_id')  // Should show OAuth2 client ID
config('services.dvsa.api_key')  // Should show DVSA API key

# Test DVSA OAuth2 token
$dvsa = new App\Services\DvsaService();
dd($dvsa->getMotHistory('AB12CDE'));  // Will auto-request OAuth2 token
```

**No reminders sent?**
```bash
# Check scheduler is running
php artisan schedule:list

# Run manually to test
php artisan mot:send-reminders --days=30

# Check queue (if using queues)
php artisan queue:work
```

**Sync failing?**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Test single vehicle
php artisan mot:sync --vehicle=1

# Reduce rate (1 second delay between requests)
```

---

## 📞 Support Resources

- **Full Documentation**: [MOT_INTEGRATION_GUIDE.md](MOT_INTEGRATION_GUIDE.md)
- **Implementation Details**: [MOT_INTEGRATION_COMPLETE.md](MOT_INTEGRATION_COMPLETE.md)
- **DVLA API Docs**: https://developer-portal.driver-vehicle-licensing.api.gov.uk/
- **DVSA API Docs**: https://documentation.history.mot.api.gov.uk/
- **Laravel Docs**: https://laravel.com/docs/scheduling

---

**Print this card and keep near your workstation! 📌**
