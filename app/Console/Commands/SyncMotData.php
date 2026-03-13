<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Models\MotTest;
use App\Services\DvsaService;
use App\Services\VehicleDataGlobalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncMotData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mot:sync 
                            {--vehicle= : Specific vehicle ID to sync}
                            {--registration= : Specific registration number to sync}
                            {--all : Sync all active vehicles}
                            {--limit=10 : Maximum number of vehicles to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync MOT data from DVSA API for vehicles';

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
        $this->info('🔄 MOT Data Synchronization');
        $this->newLine();

        // Determine which vehicles to sync
        $vehicles = $this->getVehiclesToSync();

        if ($vehicles->isEmpty()) {
            $this->warn('No vehicles found to sync.');
            return 1;
        }

        $this->info("Found {$vehicles->count()} vehicle(s) to sync.");
        $this->newLine();

        $synced = 0;
        $errors = 0;
        $testsImported = 0;

        $progressBar = $this->output->createProgressBar($vehicles->count());
        $progressBar->start();

        foreach ($vehicles as $vehicle) {
            try {
                // Fetch MOT history from DVSA
                $motHistory = $this->dvsaService->getMotHistory($vehicle->registration_number);

                if (!$motHistory) {
                    $errors++;
                    $progressBar->advance();
                    continue;
                }

                DB::beginTransaction();

                // Import MOT tests
                if (isset($motHistory[0]['motTests'])) {
                    foreach ($motHistory[0]['motTests'] as $test) {
                        $testNumber = $test['motTestNumber'] ?? null;

                        // Skip if already exists
                        if ($testNumber && MotTest::where('test_number', $testNumber)->exists()) {
                            continue;
                        }

                        // Format and store
                        $motData = $this->dvsaService->formatMotTestData($test);
                        $motData['vehicle_id'] = $vehicle->id;

                        MotTest::create($motData);
                        $testsImported++;
                    }

                    // Update vehicle with latest MOT expiry
                    $latestTest = $this->dvsaService->getLatestMotTest($motHistory);
                    if ($latestTest && isset($latestTest['expiryDate'])) {
                        $vehicle->update([
                            'mot_due_date' => Carbon::parse($latestTest['expiryDate'])->format('Y-m-d'),
                        ]);
                    }
                }

                // Refresh vehicle data from VehicleDataGlobal if key fields are missing
                if (empty($vehicle->vin) || empty($vehicle->model) || empty($vehicle->transmission)) {
                    $vdgData = $this->vdgService->getVehicleDetails($vehicle->registration_number);
                    if ($vdgData) {
                        $vdgFormatted = $this->vdgService->formatVehicleData($vdgData);
                        $updates = array_filter([
                            'vin'          => empty($vehicle->vin)          ? ($vdgFormatted['vin']          ?? null) : null,
                            'model'        => empty($vehicle->model)        ? ($vdgFormatted['model']        ?? null) : null,
                            'transmission' => empty($vehicle->transmission) ? ($vdgFormatted['transmission'] ?? null) : null,
                            'make'         => empty($vehicle->make)         ? ($vdgFormatted['make']         ?? null) : null,
                            'color'        => empty($vehicle->color)        ? ($vdgFormatted['color']        ?? null) : null,
                            'fuel_type'    => empty($vehicle->fuel_type)    ? ($vdgFormatted['fuel_type']    ?? null) : null,
                            'engine_size'  => empty($vehicle->engine_size)  ? ($vdgFormatted['engine_size']  ?? null) : null,
                            'dvla_data'    => $vdgFormatted['dvla_data'],
                        ]);
                        if (!empty($updates)) {
                            $vehicle->update($updates);
                        }
                    }
                }

                DB::commit();
                $synced++;

            } catch (\Exception $e) {
                DB::rollBack();
                $errors++;
                $this->newLine();
                $this->error("Error syncing {$vehicle->registration_number}: " . $e->getMessage());
            }

            $progressBar->advance();

            // Rate limiting: Wait 1 second between requests to avoid hitting API limits
            sleep(1);
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('=== Synchronization Complete ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Vehicles processed', $vehicles->count()],
                ['Successfully synced', $synced],
                ['MOT tests imported', $testsImported],
                ['Errors', $errors],
            ]
        );

        return 0;
    }

    /**
     * Get vehicles to sync based on command options
     */
    protected function getVehiclesToSync()
    {
        // Specific vehicle ID
        if ($vehicleId = $this->option('vehicle')) {
            return Vehicle::where('id', $vehicleId)->get();
        }

        // Specific registration
        if ($registration = $this->option('registration')) {
            return Vehicle::where('registration_number', 'LIKE', "%{$registration}%")->get();
        }

        // All active vehicles
        if ($this->option('all')) {
            return Vehicle::where('is_active', true)
                ->limit($this->option('limit'))
                ->get();
        }

        // Default: Vehicles with MOT due in next 60 days or expired
        return Vehicle::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('mot_due_date')
                      ->orWhere('mot_due_date', '<=', Carbon::now()->addDays(60));
            })
            ->limit($this->option('limit'))
            ->get();
    }
}
