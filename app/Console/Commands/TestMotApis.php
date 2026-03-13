<?php

namespace App\Console\Commands;

use App\Services\DvsaService;
use App\Services\VehicleDataGlobalService;
use Illuminate\Console\Command;

class TestMotApis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mot:test-apis {registration? : Vehicle registration to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test VehicleDataGlobal and DVSA API connectivity';

    protected $dvsaService;
    protected $vdgService;

    public function __construct()
    {
        parent::__construct();
        $this->dvsaService = new DvsaService();
        $this->vdgService  = new VehicleDataGlobalService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing MOT API Connectivity');
        $this->newLine();

        $registration = $this->argument('registration') ?? 'AB12CDE';

        // Test VehicleDataGlobal API
        $this->info('1⃣⃣  Testing VehicleDataGlobal API (Vehicle Details)...');
        $this->testVdgApi($registration);

        $this->newLine();

        // Test DVSA API
        $this->info('2️⃣  Testing DVSA API (MOT History)...');
        $this->testDvsaApi($registration);

        $this->newLine();
        $this->info('✅ API testing complete!');

        return 0;
    }

    protected function testVdgApi(string $registration)
    {
        try {
            $apiKey = config('services.vehicledataglobal.api_key');

            if (!$apiKey || $apiKey === 'your_vehicledataglobal_api_key_here') {
                $this->warn('   ⚠️  VehicleDataGlobal API key not configured');
                $this->line('   Set VDG_API_KEY in .env file');
                return;
            }

            $this->line("   Testing with registration: {$registration}");

            $dataItems = $this->vdgService->getVehicleDetails($registration);

            if ($dataItems) {
                $formatted = $this->vdgService->formatVehicleData($dataItems);
                $this->info('   ✅ VehicleDataGlobal API connected successfully!');
                $this->newLine();
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Registration', $formatted['registration_number'] ?? 'N/A'],
                        ['Make',         $formatted['make']                ?? 'N/A'],
                        ['Model',        $formatted['model']               ?? 'N/A'],
                        ['Year',         $formatted['year']                ?? 'N/A'],
                        ['Colour',       $formatted['color']               ?? 'N/A'],
                        ['Fuel Type',    $formatted['fuel_type']           ?? 'N/A'],
                        ['Transmission', $formatted['transmission']        ?? 'N/A'],
                        ['Engine (cc)',  $formatted['engine_size']         ?? 'N/A'],
                        ['VIN',          $formatted['vin']                 ?? 'N/A'],
                        ['MOT Status',   $formatted['mot_status']          ?? 'N/A'],
                        ['MOT Expiry',   $formatted['mot_due_date']        ?? 'N/A'],
                        ['Tax Status',   $formatted['tax_status']          ?? 'N/A'],
                        ['Tax Due',      $formatted['tax_due_date']        ?? 'N/A'],
                        ['CO2 (g/km)',   $formatted['co2_emissions']       ?? 'N/A'],
                        ['BHP',          $formatted['bhp']                 ?? 'N/A'],
                    ]
                );
            } else {
                $this->error('   ❌ Failed to retrieve vehicle data from VehicleDataGlobal');
                $this->line('   Check logs for details: storage/logs/laravel.log');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ VehicleDataGlobal API Error: ' . $e->getMessage());
        }
    }

    protected function testDvsaApi(string $registration)
    {
        try {
            // Check credentials
            $clientId = config('services.dvsa.client_id');
            $clientSecret = config('services.dvsa.client_secret');
            $apiKey = config('services.dvsa.api_key');

            if (!$clientId || !$clientSecret || !$apiKey) {
                $this->warn('   ⚠️  DVSA credentials not configured');
                $this->line('   Set these in .env file:');
                $this->line('   - DVSA_CLIENT_ID');
                $this->line('   - DVSA_CLIENT_SECRET');
                $this->line('   - DVSA_API_KEY');
                $this->line('   - DVSA_SCOPE');
                $this->line('   - DVSA_TOKEN_URL');
                return;
            }

            $this->line('   Credentials configured ✓');
            $this->line("   Testing with registration: {$registration}");
            $this->line('   Obtaining OAuth2 token...');

            $motHistory = $this->dvsaService->getMotHistory($registration);

            if ($motHistory) {
                $this->info('   ✅ DVSA API connected successfully!');
                $this->newLine();

                if (isset($motHistory[0]['motTests']) && count($motHistory[0]['motTests']) > 0) {
                    $latestTest = $motHistory[0]['motTests'][0];
                    
                    $this->table(
                        ['Field', 'Value'],
                        [
                            ['Registration', $motHistory[0]['registration'] ?? 'N/A'],
                            ['Make', $motHistory[0]['make'] ?? 'N/A'],
                            ['Model', $motHistory[0]['model'] ?? 'N/A'],
                            ['Total Tests', count($motHistory[0]['motTests'])],
                            ['Latest Test Date', $latestTest['completedDate'] ?? 'N/A'],
                            ['Latest Result', $latestTest['testResult'] ?? 'N/A'],
                            ['Expiry Date', $latestTest['expiryDate'] ?? 'N/A'],
                            ['Mileage', $latestTest['odometerValue'] ?? 'N/A'],
                        ]
                    );

                    // Show advisories if any
                    if (isset($latestTest['rfrAndComments']) && count($latestTest['rfrAndComments']) > 0) {
                        $this->newLine();
                        $this->line('   Recent Advisories/Failures:');
                        foreach (array_slice($latestTest['rfrAndComments'], 0, 3) as $item) {
                            $type = $item['type'] ?? 'UNKNOWN';
                            $text = $item['text'] ?? 'No text';
                            $this->line("   - [{$type}] {$text}");
                        }
                    }
                } else {
                    $this->info('   ✅ API connected but no MOT history found for this vehicle');
                }
            } else {
                $this->error('   ❌ Failed to retrieve MOT history from DVSA');
                $this->line('   Possible reasons:');
                $this->line('   - Invalid credentials');
                $this->line('   - Vehicle not found');
                $this->line('   - API authentication failed');
                $this->line('   Check logs: storage/logs/laravel.log');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ DVSA API Error: ' . $e->getMessage());
        }
    }
}
