<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EuroCarPartsService
{
    protected $apiKey;
    protected $baseUrl;
    protected $timeout = 30;

    public function __construct()
    {
        $this->apiKey = config('services.eurocarparts.api_key');
        $this->baseUrl = config('services.eurocarparts.base_url');
    }

    /**
     * Search for parts by registration and part type
     */
    public function searchByRegistration($registration, $partType = null)
    {
        if (!$this->isEnabled()) {
            return ['error' => 'Euro Car Parts API not configured'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/parts/search', [
                    'registration' => strtoupper(str_replace(' ', '', $registration)),
                    'part_type' => $partType,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Euro Car Parts API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => 'Failed to search parts'];
        } catch (\Exception $e) {
            Log::error('Euro Car Parts search exception', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => 'Service unavailable'];
        }
    }

    /**
     * Search for parts by VIN and part type
     */
    public function searchByVin($vin, $partType = null)
    {
        if (!$this->isEnabled()) {
            return ['error' => 'Euro Car Parts API not configured'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/parts/search-by-vin', [
                    'vin' => $vin,
                    'part_type' => $partType,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'Failed to search parts by VIN'];
        } catch (\Exception $e) {
            Log::error('Euro Car Parts VIN search exception', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => 'Service unavailable'];
        }
    }

    /**
     * Get part details by part number
     */
    public function getPartDetails($partNumber)
    {
        if (!$this->isEnabled()) {
            return ['error' => 'Euro Car Parts API not configured'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/parts/' . $partNumber);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'Part not found'];
        } catch (\Exception $e) {
            Log::error('Euro Car Parts part details exception', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => 'Service unavailable'];
        }
    }

    /**
     * Check stock availability
     */
    public function checkStock($partNumber, $quantity = 1, $postcode = null)
    {
        if (!$this->isEnabled()) {
            return ['error' => 'Euro Car Parts API not configured'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/stock/check', [
                    'part_number' => $partNumber,
                    'quantity' => $quantity,
                    'postcode' => $postcode,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'Failed to check stock'];
        } catch (\Exception $e) {
            Log::error('Euro Car Parts stock check exception', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => 'Service unavailable'];
        }
    }

    /**
     * Get real-time pricing
     */
    public function getPricing($partNumber, $quantity = 1)
    {
        if (!$this->isEnabled()) {
            return ['error' => 'Euro Car Parts API not configured'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/pricing', [
                    'part_number' => $partNumber,
                    'quantity' => $quantity,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'price' => $data['price'] ?? 0,
                    'vat' => $data['vat'] ?? 0,
                    'total' => $data['total'] ?? 0,
                    'discount' => $data['discount'] ?? 0,
                    'currency' => $data['currency'] ?? 'GBP',
                ];
            }

            return ['error' => 'Failed to get pricing'];
        } catch (\Exception $e) {
            Log::error('Euro Car Parts pricing exception', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => 'Service unavailable'];
        }
    }

    /**
     * Place an order
     */
    public function placeOrder($orderData)
    {
        if (!$this->isEnabled()) {
            return ['error' => 'Euro Car Parts API not configured'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/orders', $orderData);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Euro Car Parts order placement failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => 'Failed to place order'];
        } catch (\Exception $e) {
            Log::error('Euro Car Parts order exception', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => 'Service unavailable'];
        }
    }

    /**
     * Get order status
     */
    public function getOrderStatus($orderReference)
    {
        if (!$this->isEnabled()) {
            return ['error' => 'Euro Car Parts API not configured'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/orders/' . $orderReference);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'Order not found'];
        } catch (\Exception $e) {
            Log::error('Euro Car Parts order status exception', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => 'Service unavailable'];
        }
    }

    /**
     * Get delivery options
     */
    public function getDeliveryOptions($postcode, $parts = [])
    {
        if (!$this->isEnabled()) {
            return ['error' => 'Euro Car Parts API not configured'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/delivery/options', [
                    'postcode' => $postcode,
                    'parts' => $parts,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'Failed to get delivery options'];
        } catch (\Exception $e) {
            Log::error('Euro Car Parts delivery options exception', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => 'Service unavailable'];
        }
    }

    /**
     * Get part categories
     */
    public function getCategories()
    {
        if (!$this->isEnabled()) {
            return ['error' => 'Euro Car Parts API not configured'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/categories');

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'Failed to get categories'];
        } catch (\Exception $e) {
            Log::error('Euro Car Parts categories exception', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Search by part description
     */
    public function searchByDescription($description, $make = null, $model = null)
    {
        if (!$this->isEnabled()) {
            return ['error' => 'Euro Car Parts API not configured'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/parts/search-description', [
                    'description' => $description,
                    'make' => $make,
                    'model' => $model,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'No parts found'];
        } catch (\Exception $e) {
            Log::error('Euro Car Parts description search exception', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => 'Service unavailable'];
        }
    }

    /**
     * Check if the service is enabled and configured
     */
    public function isEnabled()
    {
        return !empty($this->apiKey) && !empty($this->baseUrl);
    }

    /**
     * Format part data for local storage
     */
    public function formatPartForStorage($apiPart)
    {
        return [
            'part_number' => $apiPart['part_number'] ?? '',
            'name' => $apiPart['name'] ?? $apiPart['description'] ?? '',
            'description' => $apiPart['description'] ?? '',
            'manufacturer' => $apiPart['manufacturer'] ?? 'Euro Car Parts',
            'category' => $apiPart['category'] ?? 'General',
            'unit_price' => $apiPart['price'] ?? 0,
            'cost_price' => $apiPart['trade_price'] ?? ($apiPart['price'] ?? 0) * 0.75,
            'supplier' => 'eurocarparts',
            'supplier_part_number' => $apiPart['part_number'] ?? '',
            'stock_quantity' => 0, // Don't track ECP stock locally
            'reorder_level' => 0,
        ];
    }
}
