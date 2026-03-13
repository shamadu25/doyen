<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Parts Supplier Service
 * 
 * Integrates with multiple parts supplier APIs:
 * - Euro Car Parts
 * - GSF Car Parts
 * - Auto Doc
 * - Oscaro
 * - Generic suppliers with custom API endpoints
 */
class PartsSupplierService
{
    protected $suppliers = [];
    protected $defaultSupplier;

    public function __construct()
    {
        $this->suppliers = [
            'eurocarparts' => [
                'name' => 'Euro Car Parts',
                'enabled' => !empty(config('services.eurocarparts.api_key')),
                'api_key' => config('services.eurocarparts.api_key'),
                'base_url' => config('services.eurocarparts.base_url'),
                'timeout' => 30,
            ],
            'gsf' => [
                'name' => 'GSF Car Parts',
                'enabled' => !empty(config('services.gsf.api_key')),
                'api_key' => config('services.gsf.api_key'),
                'base_url' => config('services.gsf.base_url'),
                'timeout' => 30,
            ],
            'autodoc' => [
                'name' => 'Auto Doc',
                'enabled' => !empty(config('services.autodoc.api_key')),
                'api_key' => config('services.autodoc.api_key'),
                'base_url' => config('services.autodoc.base_url'),
                'timeout' => 30,
            ],
            'oscaro' => [
                'name' => 'Oscaro',
                'enabled' => !empty(config('services.oscaro.api_key')),
                'api_key' => config('services.oscaro.api_key'),
                'base_url' => config('services.oscaro.base_url'),
                'timeout' => 30,
            ],
        ];

        $this->defaultSupplier = config('services.parts_supplier.default', 'eurocarparts');
    }

    /**
     * Search for parts across all enabled suppliers
     */
    public function searchParts($query, $vehicleReg = null, $supplier = null)
    {
        if ($supplier) {
            return $this->searchFromSupplier($supplier, $query, $vehicleReg);
        }

        // Search all enabled suppliers
        $results = [];
        foreach ($this->suppliers as $key => $config) {
            if ($config['enabled']) {
                try {
                    $supplierResults = $this->searchFromSupplier($key, $query, $vehicleReg);
                    if (!empty($supplierResults)) {
                        $results[$key] = $supplierResults;
                    }
                } catch (\Exception $e) {
                    Log::warning("Parts search failed for supplier {$key}", [
                        'error' => $e->getMessage(),
                        'query' => $query,
                    ]);
                }
            }
        }

        return $results;
    }

    /**
     * Search parts from a specific supplier
     */
    protected function searchFromSupplier($supplier, $query, $vehicleReg = null)
    {
        $config = $this->suppliers[$supplier] ?? null;
        
        if (!$config || !$config['enabled']) {
            throw new \Exception("Supplier {$supplier} is not configured or enabled");
        }

        // Cache key
        $cacheKey = "parts_search_{$supplier}_{$query}_" . ($vehicleReg ?? 'no_reg');
        
        return Cache::remember($cacheKey, 3600, function () use ($supplier, $config, $query, $vehicleReg) {
            switch ($supplier) {
                case 'eurocarparts':
                    return $this->searchEuroCarParts($config, $query, $vehicleReg);
                case 'gsf':
                    return $this->searchGSF($config, $query, $vehicleReg);
                case 'autodoc':
                    return $this->searchAutoDoc($config, $query, $vehicleReg);
                case 'oscaro':
                    return $this->searchOscaro($config, $query, $vehicleReg);
                default:
                    return $this->searchGeneric($config, $query, $vehicleReg);
            }
        });
    }

    /**
     * Euro Car Parts API integration
     */
    protected function searchEuroCarParts($config, $query, $vehicleReg)
    {
        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'] . '/parts/search', [
                'q' => $query,
                'registration' => $vehicleReg,
            ]);

        if ($response->successful()) {
            return $this->normalizeResults($response->json(), 'eurocarparts');
        }

        Log::error('Euro Car Parts API error', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [];
    }

    /**
     * GSF Car Parts API integration
     */
    protected function searchGSF($config, $query, $vehicleReg)
    {
        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'X-API-Key' => $config['api_key'],
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'] . '/search', [
                'search' => $query,
                'vrm' => $vehicleReg,
            ]);

        if ($response->successful()) {
            return $this->normalizeResults($response->json(), 'gsf');
        }

        Log::error('GSF API error', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [];
    }

    /**
     * Auto Doc API integration
     */
    protected function searchAutoDoc($config, $query, $vehicleReg)
    {
        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => 'ApiKey ' . $config['api_key'],
                'Accept' => 'application/json',
            ])
            ->post($config['base_url'] . '/articles/search', [
                'searchQuery' => $query,
                'vehicleRegistration' => $vehicleReg,
            ]);

        if ($response->successful()) {
            return $this->normalizeResults($response->json(), 'autodoc');
        }

        Log::error('Auto Doc API error', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [];
    }

    /**
     * Oscaro API integration
     */
    protected function searchOscaro($config, $query, $vehicleReg)
    {
        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'X-API-Token' => $config['api_key'],
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'] . '/products/search', [
                'keywords' => $query,
                'registration' => $vehicleReg,
            ]);

        if ($response->successful()) {
            return $this->normalizeResults($response->json(), 'oscaro');
        }

        Log::error('Oscaro API error', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [];
    }

    /**
     * Generic supplier API integration
     */
    protected function searchGeneric($config, $query, $vehicleReg)
    {
        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'] . '/search', [
                'q' => $query,
                'vehicle' => $vehicleReg,
            ]);

        if ($response->successful()) {
            return $this->normalizeResults($response->json(), 'generic');
        }

        return [];
    }

    /**
     * Normalize results from different suppliers into a common format
     */
    protected function normalizeResults($data, $supplier)
    {
        $normalized = [];

        switch ($supplier) {
            case 'eurocarparts':
                foreach ($data['products'] ?? [] as $product) {
                    $normalized[] = [
                        'supplier' => 'Euro Car Parts',
                        'supplier_code' => 'eurocarparts',
                        'part_number' => $product['partNumber'] ?? '',
                        'supplier_part_number' => $product['id'] ?? '',
                        'name' => $product['name'] ?? '',
                        'description' => $product['description'] ?? '',
                        'manufacturer' => $product['brand'] ?? '',
                        'category' => $product['category'] ?? '',
                        'price' => $product['price']['value'] ?? 0,
                        'vat_rate' => $product['price']['vat'] ?? 20,
                        'availability' => $product['stock']['available'] ?? false,
                        'stock_quantity' => $product['stock']['quantity'] ?? 0,
                        'delivery_days' => $product['delivery']['days'] ?? 1,
                        'image_url' => $product['image'] ?? null,
                        'url' => $product['url'] ?? null,
                    ];
                }
                break;

            case 'gsf':
                foreach ($data['results'] ?? [] as $item) {
                    $normalized[] = [
                        'supplier' => 'GSF Car Parts',
                        'supplier_code' => 'gsf',
                        'part_number' => $item['partNo'] ?? '',
                        'supplier_part_number' => $item['gsfCode'] ?? '',
                        'name' => $item['description'] ?? '',
                        'description' => $item['longDescription'] ?? '',
                        'manufacturer' => $item['manufacturer'] ?? '',
                        'category' => $item['type'] ?? '',
                        'price' => $item['tradePrice'] ?? 0,
                        'vat_rate' => 20,
                        'availability' => $item['inStock'] ?? false,
                        'stock_quantity' => $item['stockLevel'] ?? 0,
                        'delivery_days' => $item['deliveryDays'] ?? 1,
                        'image_url' => $item['imageUrl'] ?? null,
                        'url' => null,
                    ];
                }
                break;

            case 'autodoc':
                foreach ($data['articles'] ?? [] as $article) {
                    $normalized[] = [
                        'supplier' => 'Auto Doc',
                        'supplier_code' => 'autodoc',
                        'part_number' => $article['articleNumber'] ?? '',
                        'supplier_part_number' => $article['id'] ?? '',
                        'name' => $article['name'] ?? '',
                        'description' => $article['description'] ?? '',
                        'manufacturer' => $article['brandName'] ?? '',
                        'category' => $article['genericArticle'] ?? '',
                        'price' => $article['price']['gross'] ?? 0,
                        'vat_rate' => 20,
                        'availability' => $article['available'] ?? false,
                        'stock_quantity' => $article['stock'] ?? 0,
                        'delivery_days' => $article['deliveryDays'] ?? 2,
                        'image_url' => $article['imageUrl'] ?? null,
                        'url' => $article['productUrl'] ?? null,
                    ];
                }
                break;

            case 'oscaro':
                foreach ($data['items'] ?? [] as $item) {
                    $normalized[] = [
                        'supplier' => 'Oscaro',
                        'supplier_code' => 'oscaro',
                        'part_number' => $item['reference'] ?? '',
                        'supplier_part_number' => $item['oscaroId'] ?? '',
                        'name' => $item['title'] ?? '',
                        'description' => $item['desc'] ?? '',
                        'manufacturer' => $item['brand'] ?? '',
                        'category' => $item['family'] ?? '',
                        'price' => $item['priceExclVat'] ?? 0,
                        'vat_rate' => 20,
                        'availability' => $item['available'] ?? false,
                        'stock_quantity' => $item['qty'] ?? 0,
                        'delivery_days' => $item['deliveryTime'] ?? 3,
                        'image_url' => $item['img'] ?? null,
                        'url' => $item['link'] ?? null,
                    ];
                }
                break;

            default:
                // Generic format - assume it's already normalized
                return $data;
        }

        return $normalized;
    }

    /**
     * Get part details from supplier
     */
    public function getPartDetails($supplier, $partNumber)
    {
        $config = $this->suppliers[$supplier] ?? null;
        
        if (!$config || !$config['enabled']) {
            throw new \Exception("Supplier {$supplier} is not configured or enabled");
        }

        $cacheKey = "part_details_{$supplier}_{$partNumber}";
        
        return Cache::remember($cacheKey, 7200, function () use ($supplier, $config, $partNumber) {
            $response = Http::timeout($config['timeout'])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $config['api_key'],
                    'Accept' => 'application/json',
                ])
                ->get($config['base_url'] . '/parts/' . $partNumber);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        });
    }

    /**
     * Place an order with supplier
     */
    public function placeOrder($supplier, $items, $deliveryAddress = null)
    {
        $config = $this->suppliers[$supplier] ?? null;
        
        if (!$config || !$config['enabled']) {
            throw new \Exception("Supplier {$supplier} is not configured or enabled");
        }

        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($config['base_url'] . '/orders', [
                'items' => $items,
                'delivery' => $deliveryAddress,
            ]);

        if ($response->successful()) {
            Log::info("Order placed with {$supplier}", [
                'order_id' => $response->json()['orderId'] ?? null,
                'items_count' => count($items),
            ]);

            return $response->json();
        }

        Log::error("Failed to place order with {$supplier}", [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new \Exception("Failed to place order: " . $response->body());
    }

    /**
     * Check order status
     */
    public function checkOrderStatus($supplier, $orderId)
    {
        $config = $this->suppliers[$supplier] ?? null;
        
        if (!$config || !$config['enabled']) {
            throw new \Exception("Supplier {$supplier} is not configured or enabled");
        }

        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'] . '/orders/' . $orderId);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Get list of enabled suppliers
     */
    public function getEnabledSuppliers()
    {
        return collect($this->suppliers)
            ->filter(fn($config) => $config['enabled'])
            ->map(fn($config) => $config['name'])
            ->toArray();
    }

    /**
     * Check if any supplier is configured
     */
    public function hasAnySupplierConfigured()
    {
        return collect($this->suppliers)->contains(fn($config) => $config['enabled']);
    }
}
