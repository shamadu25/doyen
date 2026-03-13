<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TecDocService
{
    protected $apiKey;
    protected $baseUrl;
    protected $providerId;

    public function __construct()
    {
        $this->apiKey = config('services.tecdoc.api_key');
        $this->providerId = config('services.tecdoc.provider_id');
        $this->baseUrl = 'https://webservice.tecalliance.services/pegasus-3-0/services/TecdocToCatDLB.jsonEndpoint';
    }

    /**
     * Search for vehicle by registration or VIN
     *
     * @param string $registration
     * @return array|null
     */
    public function searchVehicle(string $registration): ?array
    {
        try {
            $cacheKey = 'tecdoc_vehicle_' . $registration;
            
            return Cache::remember($cacheKey, 86400, function () use ($registration) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl, [
                    'getVehiclesByVIN4' => [
                        'vin' => $registration,
                        'provider' => $this->providerId,
                        'lang' => 'en',
                    ],
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('TecDoc API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            });
        } catch (\Exception $e) {
            Log::error('TecDoc API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get parts for a specific vehicle
     *
     * @param int $vehicleId TecDoc vehicle ID
     * @param string|null $category Part category
     * @return array|null
     */
    public function getVehicleParts(int $vehicleId, ?string $category = null): ?array
    {
        try {
            $cacheKey = 'tecdoc_parts_' . $vehicleId . '_' . ($category ?? 'all');
            
            return Cache::remember($cacheKey, 3600, function () use ($vehicleId, $category) {
                $payload = [
                    'getArticles' => [
                        'articleCountry' => 'GB',
                        'lang' => 'en',
                        'provider' => $this->providerId,
                        'linkageTargetId' => $vehicleId,
                        'linkageTargetType' => 'P',
                    ],
                ];

                if ($category) {
                    $payload['getArticles']['assemblyGroupNodeIds'] = [$category];
                }

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl, $payload);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            });
        } catch (\Exception $e) {
            Log::error('TecDoc parts API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Search for parts by name or number
     *
     * @param string $searchTerm
     * @param int|null $vehicleId
     * @return array|null
     */
    public function searchParts(string $searchTerm, ?int $vehicleId = null): ?array
    {
        try {
            $payload = [
                'findArticles' => [
                    'searchQuery' => $searchTerm,
                    'articleCountry' => 'GB',
                    'lang' => 'en',
                    'provider' => $this->providerId,
                ],
            ];

            if ($vehicleId) {
                $payload['findArticles']['linkageTargetId'] = $vehicleId;
                $payload['findArticles']['linkageTargetType'] = 'P';
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('TecDoc search error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get detailed part information
     *
     * @param int $articleId
     * @return array|null
     */
    public function getPartDetails(int $articleId): ?array
    {
        try {
            $cacheKey = 'tecdoc_part_' . $articleId;
            
            return Cache::remember($cacheKey, 3600, function () use ($articleId) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl, [
                    'getArticleDirectSearchAllNumbers' => [
                        'articleNumber' => (string)$articleId,
                        'articleCountry' => 'GB',
                        'lang' => 'en',
                        'provider' => $this->providerId,
                    ],
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            });
        } catch (\Exception $e) {
            Log::error('TecDoc part details error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get service schedules for a vehicle
     *
     * @param int $vehicleId
     * @return array|null
     */
    public function getServiceSchedule(int $vehicleId): ?array
    {
        try {
            $cacheKey = 'tecdoc_service_' . $vehicleId;
            
            return Cache::remember($cacheKey, 86400, function () use ($vehicleId) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl, [
                    'getServiceSchedules' => [
                        'linkageTargetId' => $vehicleId,
                        'linkageTargetType' => 'P',
                        'lang' => 'en',
                        'provider' => $this->providerId,
                    ],
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            });
        } catch (\Exception $e) {
            Log::error('TecDoc service schedule error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Format TecDoc vehicle data for storage
     *
     * @param array $tecDocData
     * @return array
     */
    public function formatVehicleData(array $tecDocData): array
    {
        return [
            'tecdoc_vehicle_id' => $tecDocData['vehicleId'] ?? null,
            'manufacturer' => $tecDocData['manuName'] ?? null,
            'model_series' => $tecDocData['modelName'] ?? null,
            'engine_code' => $tecDocData['engineCode'] ?? null,
            'power_hp' => $tecDocData['powerHpFrom'] ?? null,
            'power_kw' => $tecDocData['powerKwFrom'] ?? null,
            'tecdoc_data' => $tecDocData,
        ];
    }
}
