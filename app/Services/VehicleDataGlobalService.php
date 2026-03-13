<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class VehicleDataGlobalService
{
    protected string $apiKey = '';
    protected string $baseUrl;

    public function __construct()
    {
        // env() may resolve to null in production if key is missing; normalize to string.
        $this->apiKey  = (string) (config('services.vehicledataglobal.api_key') ?? '');
        $this->baseUrl = config(
            'services.vehicledataglobal.base_url',
            'https://uk1.ukvehicledata.co.uk/api/datapackage/VehicleAndMotHistory'
        );
    }

    /**
     * Look up a vehicle by UK registration plate.
     * Returns the DataItems array from the API response, or null on failure.
     * Results are cached for 24 hours to reduce API credit consumption.
     */
    public function getVehicleDetails(string $registrationNumber): ?array
    {
        $vrm      = strtoupper(str_replace(' ', '', $registrationNumber));
        $cacheKey = 'vdg_vehicle_' . $vrm;

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($vrm) {
            return $this->fetchFromApi($vrm);
        });
    }

    /**
     * Force-refresh a vehicle's data from the API (bypasses 24-hour cache).
     */
    public function refreshVehicleDetails(string $registrationNumber): ?array
    {
        $vrm      = strtoupper(str_replace(' ', '', $registrationNumber));
        $cacheKey = 'vdg_vehicle_' . $vrm;

        Cache::forget($cacheKey);

        $data = $this->fetchFromApi($vrm);

        if ($data) {
            Cache::put($cacheKey, $data, now()->addHours(24));
        }

        return $data;
    }

    /**
     * Make the HTTP call to the UKVehicleData / VehicleDataGlobal API.
     *
     * Endpoint: https://uk1.ukvehicledata.co.uk/api/datapackage/VehicleAndMotHistory
     * Response root: { "DataItems": { "VehicleRegistration": {...}, "VehicleStatus": {...}, ... } }
     */
    protected function fetchFromApi(string $vrm): ?array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl, [
                'v'             => '2',
                'api_nullitems' => '1',
                'auth_apikey'   => $this->apiKey,
                'key_vrm'       => $vrm,
            ]);

            if (!$response->successful()) {
                Log::warning('VehicleDataGlobal API request failed', [
                    'vrm'    => $vrm,
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 500),
                ]);

                return null;
            }

            $body = $response->json();

            // Response wrapper: { "Response": { "StatusCode": "Success", "DataItems": {...} } }
            $statusCode = $body['Response']['StatusCode'] ?? '';

            if (strtolower($statusCode) !== 'success') {
                Log::warning('VehicleDataGlobal API returned non-success status', [
                    'vrm'    => $vrm,
                    'status' => $statusCode,
                ]);
                return null;
            }

            $dataItems = $body['Response']['DataItems'] ?? null;

            if (empty($dataItems)) {
                Log::warning('VehicleDataGlobal API returned empty DataItems', ['vrm' => $vrm]);
                return null;
            }

            return $dataItems;

        } catch (\Exception $e) {
            Log::error('VehicleDataGlobal API error: ' . $e->getMessage(), ['vrm' => $vrm]);
            return null;
        }
    }

    /**
     * Map a VehicleDataGlobal DataItems array to the vehicle fields used throughout the app.
     *
     * Key source fields:
     *   DataItems.VehicleRegistration  - DVLA registration record
     *   DataItems.VehicleStatus        - MOT status
     *   DataItems.TechnicalDetails     - engine, performance, dimensions
     *   DataItems.SmmtDetails          - SMMT classification (body style, variant, range)
     *   DataItems.MotHistory           - full MOT history
     */
    public function formatVehicleData(array $dataItems): array
    {
        $reg  = $dataItems['VehicleRegistration'] ?? [];
        $stat = $dataItems['VehicleStatus']       ?? [];
        $tech = $dataItems['TechnicalDetails']    ?? [];
        $smmt = $dataItems['SmmtDetails']         ?? [];

        // MOT due date - format returned is "DD/MM/YYYY"
        $motDueDate = $this->parseDateDmy($stat['NextMotDueDate'] ?? null);

        // Date first registered - ISO format "2017-06-19T00:00:00"
        $dateFirstRegistered = $this->parseDate($reg['DateFirstRegistered'] ?? null);

        // BHP / Torque from TechnicalDetails
        $bhp    = $tech['Performance']['Power']['Bhp']  ?? null;
        $torque = $tech['Performance']['Torque']['Nm']  ?? null;
        $co2    = $tech['Performance']['Co2']            ?? ($reg['Co2Emissions'] ?? null);

        // Engine capacity - stored as string "1399" in VehicleRegistration
        $engineCc = isset($reg['EngineCapacity']) && is_numeric($reg['EngineCapacity'])
            ? (int) $reg['EngineCapacity'] : null;

        return [
            'registration_number'    => $reg['Vrm']                      ?? null,
            'vin'                    => $this->extractVin($reg['Vin']     ?? null),
            'make'                   => $reg['Make']                      ?? null,
            'model'                  => $reg['Model']                     ?? null,
            'variant'                => $smmt['ModelVariant']             ?? ($smmt['Trim'] ?? null),
            'color'                  => $reg['Colour']                    ?? null,
            'year'                   => isset($reg['YearOfManufacture'])
                                            ? (int) $reg['YearOfManufacture'] : null,
            'fuel_type'              => $reg['FuelType']                  ?? null,
            'engine_size'            => $engineCc,
            'transmission'           => $reg['TransmissionType']          ?? null,
            'gear_count'             => $reg['GearCount']                 ?? null,
            'body_style'             => $smmt['BodyStyle']                ?? ($reg['DoorPlanLiteral'] ?? null),
            'number_of_doors'        => $tech['Dimensions']['NumberOfDoors'] ?? null,
            'number_of_seats'        => $reg['SeatingCapacity']           ?? ($tech['Dimensions']['NumberOfSeats'] ?? null),
            'wheelplan'              => $reg['WheelPlan']                 ?? null,
            'vehicle_type'           => $reg['VehicleClass']              ?? null,
            'mot_status'             => isset($stat['DaysUntilNextMotIsDue']) ? 'Valid' : null,
            'mot_due_date'           => $motDueDate,
            'mot_days_remaining'     => $stat['DaysUntilNextMotIsDue']    ?? null,
            'tax_status'             => null, // not in VehicleAndMotHistory package
            'tax_due_date'           => null,
            'co2_emissions'          => $co2 ? (int) $co2 : null,
            'bhp'                    => $bhp ? (float) $bhp : null,
            'torque_nm'              => $torque ? (float) $torque : null,
            'month_first_registered' => $reg['YearMonthFirstRegistered']  ?? null,
            'date_first_registered'  => $dateFirstRegistered,
            'previous_vrm'           => $reg['PreviousVrmGb']             ?? null,
            'mot_history'            => $dataItems['MotHistory']          ?? null,
            // Raw DataItems stored as dvla_data for backward compatibility
            'dvla_data'              => $dataItems,
        ];
    }

    /**
     * Check if MOT is due within the given number of days.
     */
    public function isMotDueSoon(string $motExpiryDate, int $daysThreshold = 30): bool
    {
        try {
            $expiry = Carbon::createFromFormat('d/m/Y', $motExpiryDate)
                      ?? Carbon::parse($motExpiryDate);

            return $expiry->isFuture() && $expiry->diffInDays(now()) <= $daysThreshold;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if road tax is due within the given number of days.
     */
    public function isTaxDueSoon(string $taxDueDate, int $daysThreshold = 14): bool
    {
        try {
            $due = Carbon::parse($taxDueDate);
            return $due->isFuture() && $due->diffInDays(now()) <= $daysThreshold;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Parse a "DD/MM/YYYY" date string (as returned by VehicleStatus.NextMotDueDate).
     */
    protected function parseDateDmy(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return $this->parseDate($value);
        }
    }

    /**
     * Parse an ISO/mixed date string (e.g. "2017-06-19T00:00:00").
     * Returns Y-m-d or null.
     */
    protected function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * VDG returns a message in the VIN field on free/trial accounts:
     * "For access to the full VIN / Chassis number please visit here: https://..."
     * In that case return null so the field stays blank.
     */
    protected function extractVin(?string $vin): ?string
    {
        if (empty($vin)) {
            return null;
        }

        if (strlen($vin) > 20 || str_contains($vin, 'http')) {
            return null;
        }

        return $vin;
    }
}
