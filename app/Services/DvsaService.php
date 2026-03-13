<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DvsaService
{
    protected $clientId;
    protected $clientSecret;
    protected $apiKey;
    protected $scope;
    protected $tokenUrl;
    protected $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.dvsa.client_id');
        $this->clientSecret = config('services.dvsa.client_secret');
        $this->apiKey = config('services.dvsa.api_key');
        $this->scope = config('services.dvsa.scope');
        $this->tokenUrl = config('services.dvsa.token_url');
        $this->baseUrl = config('services.dvsa.base_url');
    }

    /**
     * Get OAuth2 access token (cached for 55 minutes)
     *
     * @return string|null
     */
    protected function getAccessToken(): ?string
    {
        return Cache::remember('dvsa_access_token', 3300, function () {
            try {
                $response = Http::asForm()->post($this->tokenUrl, [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => $this->scope,
                    'grant_type' => 'client_credentials',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['access_token'] ?? null;
                }

                Log::error('DVSA OAuth2 token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('DVSA OAuth2 error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Get MOT history for a vehicle by registration number
     *
     * @param string $registrationNumber
     * @return array|null
     */
    public function getMotHistory(string $registrationNumber): ?array
    {
        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                Log::error('Failed to obtain DVSA access token');
                return null;
            }

            $cleanReg = strtoupper(str_replace(' ', '', $registrationNumber));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'x-api-key' => $this->apiKey,
                'Accept' => 'application/json+v6',
                'User-Agent' => 'DOYEN-AUTO-GARAGE/1.0',
                'Content-Type' => 'application/json',
            ])->timeout(30)
              ->get($this->baseUrl, [
                  'registration' => $cleanReg,
              ]);

            if ($response->successful()) {
                return $response->json();
            }

            // Log detailed error for debugging
            Log::warning('DVSA API request failed', [
                'status' => $response->status(),
                'registration' => $cleanReg,
                'headers' => $response->headers(),
                'body' => substr($response->body(), 0, 500), // First 500 chars
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('DVSA API error: ' . $e->getMessage(), [
                'registration' => $registrationNumber,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Get latest MOT test from history
     *
     * @param array $motHistory
     * @return array|null
     */
    public function getLatestMotTest(array $motHistory): ?array
    {
        if (!isset($motHistory[0]['motTests']) || empty($motHistory[0]['motTests'])) {
            return null;
        }

        return $motHistory[0]['motTests'][0];
    }

    /**
     * Parse MOT advisories and failures
     *
     * @param array $motTest
     * @return array
     */
    public function parseMotDefects(array $motTest): array
    {
        $advisories = [];
        $failures = [];

        if (isset($motTest['rfrAndComments'])) {
            foreach ($motTest['rfrAndComments'] as $defect) {
                if ($defect['type'] === 'ADVISORY') {
                    $advisories[] = [
                        'text' => $defect['text'],
                        'dangerous' => $defect['dangerous'] ?? false,
                    ];
                } elseif (in_array($defect['type'], ['FAIL', 'PRS', 'MAJOR', 'DANGEROUS'])) {
                    $failures[] = [
                        'text' => $defect['text'],
                        'type' => $defect['type'],
                        'dangerous' => $defect['dangerous'] ?? false,
                    ];
                }
            }
        }

        return [
            'advisories' => $advisories,
            'failures' => $failures,
        ];
    }

    /**
     * Format MOT test data for storage
     *
     * @param array $motTest
     * @return array
     */
    public function formatMotTestData(array $motTest): array
    {
        $defects = $this->parseMotDefects($motTest);

        return [
            'test_number' => $motTest['motTestNumber'] ?? null,
            'test_date' => isset($motTest['completedDate']) ? 
                \Carbon\Carbon::parse($motTest['completedDate'])->format('Y-m-d H:i:s') : null,
            'expiry_date' => isset($motTest['expiryDate']) ? 
                \Carbon\Carbon::parse($motTest['expiryDate'])->format('Y-m-d') : null,
            'mileage' => $motTest['odometerValue'] ?? 0,
            'test_result' => strtolower($motTest['testResult'] ?? 'unknown'),
            'advisories' => $defects['advisories'],
            'failures' => $defects['failures'],
            'dvsa_data' => $motTest,
        ];
    }

    /**
     * Check if vehicle has current MOT
     *
     * @param array $motHistory
     * @return bool
     */
    public function hasValidMot(array $motHistory): bool
    {
        $latestTest = $this->getLatestMotTest($motHistory);
        
        if (!$latestTest) {
            return false;
        }

        return isset($latestTest['expiryDate']) && 
               \Carbon\Carbon::parse($latestTest['expiryDate'])->isFuture();
    }
}
