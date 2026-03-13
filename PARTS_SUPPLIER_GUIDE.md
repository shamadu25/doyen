# Parts Supplier Integration Guide

## Overview

Budget-friendly parts catalog integration using supplier APIs instead of TecDoc.

## Supported Suppliers

### 1. **Euro Car Parts** (UK) ⭐ Recommended
- **Website**: https://www.eurocarparts.com/trade
- **Coverage**: UK, extensive stock
- **Cost**: Free for trade accounts
- **API**: REST API with OAuth2
- **How to Register**:
  1. Visit https://www.eurocarparts.com/trade
  2. Register for Trade Account
  3. Contact API support: trade@eurocarparts.com
  4. Request API access credentials
  5. Add to `.env`: `EUROCARPARTS_API_KEY=your_key_here`

### 2. **GSF Car Parts** (UK)
- **Website**: https://www.gsfcarparts.com/trade
- **Coverage**: UK, professional parts
- **Cost**: Free for trade accounts
- **API**: REST API
- **How to Register**:
  1. Visit https://www.gsfcarparts.com/trade
  2. Apply for Trade Account
  3. Contact: trade@gsfcarparts.com
  4. Request API documentation
  5. Add to `.env`: `GSF_API_KEY=your_key_here`

### 3. **Auto Doc** (EU/UK)
- **Website**: https://www.autodoc.co.uk/info/business
- **Coverage**: Europe-wide
- **Cost**: Free partnership program
- **API**: SOAP/REST
- **How to Register**:
  1. Visit https://www.autodoc.co.uk/info/business
  2. Register for Business Account
  3. Email: b2b@autodoc.de
  4. Request API integration
  5. Add to `.env`: `AUTODOC_API_KEY=your_key_here`

### 4. **Oscaro** (EU/UK)
- **Website**: https://www.oscaro.com/pro
- **Coverage**: Europe
- **Cost**: Partnership program
- **API**: REST API
- **How to Register**:
  1. Visit https://www.oscaro.com/pro
  2. Register for Pro Account
  3. Contact your account manager
  4. Request API credentials
  5. Add to `.env`: `OSCARO_API_KEY=your_key_here`

## Configuration

### Step 1: Add Supplier Credentials

Edit `.env` file:

```env
# Parts Supplier APIs
PARTS_SUPPLIER_DEFAULT=eurocarparts

# Euro Car Parts
EUROCARPARTS_API_KEY=your_api_key_here
EUROCARPARTS_API_URL=https://api.eurocarparts.com/v1

# GSF Car Parts
GSF_API_KEY=your_api_key_here
GSF_API_URL=https://api.gsfcarparts.com/v1

# Auto Doc
AUTODOC_API_KEY=your_api_key_here
AUTODOC_API_URL=https://webservice.autodoc.de/api

# Oscaro
OSCARO_API_KEY=your_api_key_here
OSCARO_API_URL=https://api.oscaro.com/v1
```

### Step 2: Test API Connection

```bash
php artisan tinker
```

```php
$service = app(\App\Services\PartsSupplierService::class);

// Check configured suppliers
$suppliers = $service->getEnabledSuppliers();
dd($suppliers);

// Test search
$results = $service->searchParts('brake pads', 'AB12CDE');
dd($results);
```

## Usage

### 1. Search Parts via Web Interface

Navigate to: **Admin → Parts → Search Suppliers**

**URL**: `/admin/parts-catalog/search-suppliers`

**Features**:
- Search by part name/number
- Filter by vehicle registration (optional)
- Select specific supplier or search all
- View real-time pricing and availability
- Import parts directly to your catalog

### 2. Search Parts Programmatically

```php
use App\Services\PartsSupplierService;

$supplierService = app(PartsSupplierService::class);

// Search all suppliers
$results = $supplierService->searchParts('oil filter');

// Search specific supplier
$results = $supplierService->searchParts('brake pads', null, 'eurocarparts');

// Search with vehicle registration
$results = $supplierService->searchParts('air filter', 'MT58FLA');
```

### 3. Import Parts to Catalog

**Via Web Interface**:
1. Search for parts
2. Click "Import" button
3. Set markup percentage (default 30%)
4. Set initial stock levels
5. Click "Import Part"

**Programmatically**:
```php
use App\Http\Controllers\PartController;

$request = new \Illuminate\Http\Request([
    'supplier' => 'eurocarparts',
    'supplier_part_number' => 'ECP123456',
    'part_number' => 'BP1234',
    'name' => 'Front Brake Pads',
    'manufacturer' => 'Brembo',
    'category' => 'Brakes',
    'cost_price' => 45.99,
    'markup_percentage' => 35,
    'stock_quantity' => 5,
    'minimum_stock' => 2,
]);

$controller = app(PartController::class);
$controller->importFromSupplier($request);
```

### 4. Place Orders with Suppliers

**API Endpoint**: `POST /admin/parts/order-from-supplier`

```javascript
// Example: Order parts via AJAX
fetch('/admin/parts/order-from-supplier', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        supplier: 'eurocarparts',
        items: [
            { part_number: 'BP1234', quantity: 4 },
            { part_number: 'OF5678', quantity: 2 }
        ]
    })
})
.then(response => response.json())
.then(data => {
    console.log('Order placed:', data.order);
});
```

### 5. Check Order Status

**API Endpoint**: `GET /admin/parts/supplier-order/{supplier}/{orderId}`

```php
$response = Http::get('/admin/parts/supplier-order/eurocarparts/ORD123456');
$status = $response->json();
```

## API Response Format

All supplier results are normalized to a common format:

```php
[
    'supplier' => 'Euro Car Parts',
    'supplier_code' => 'eurocarparts',
    'part_number' => 'BP1234',
    'supplier_part_number' => 'ECP123456',
    'name' => 'Front Brake Pads Set',
    'description' => 'Fits: Ford Focus 2015-2020',
    'manufacturer' => 'Brembo',
    'category' => 'Brakes',
    'price' => 45.99,                    // Trade price (exc VAT)
    'vat_rate' => 20,                    // VAT percentage
    'availability' => true,               // In stock
    'stock_quantity' => 12,              // Units available
    'delivery_days' => 1,                // Delivery time
    'image_url' => 'https://...',        // Product image
    'url' => 'https://...',              // Supplier product page
]
```

## Cost Comparison

| Supplier | Setup Cost | Annual Fee | Per-Request Cost | Trade Discount |
|----------|-----------|------------|------------------|----------------|
| **Euro Car Parts** | Free | Free | Free | 20-40% off retail |
| **GSF Car Parts** | Free | Free | Free | 25-45% off retail |
| **Auto Doc** | Free | Free | Free | 15-30% off retail |
| **Oscaro** | Free | Free | Free | 20-35% off retail |
| **TecDoc** | €500+ | €1,500-10,000 | - | Data only |

## Best Practices

### 1. Markup Strategy
```php
// Standard markup percentages
$markups = [
    'consumables' => 40,      // Oil, filters, bulbs
    'brakes' => 35,           // Brake pads, discs
    'electrical' => 45,       // Batteries, alternators
    'suspension' => 35,       // Shocks, springs
    'body_parts' => 50,       // Bumpers, panels
];
```

### 2. Stock Management
```php
// Import with automatic reorder levels
$part = Part::create([
    'stock_quantity' => 0,           // Don't hold stock initially
    'minimum_stock' => 0,            // Order on demand
    'supplier' => 'eurocarparts',    // Primary supplier
]);
```

### 3. Price Updates
```bash
# Update prices weekly
php artisan schedule:run

# Or manually refresh specific parts
php artisan tinker
```

```php
// Refresh prices for all supplier parts
$parts = Part::whereNotNull('supplier')->get();
foreach ($parts as $part) {
    $details = app(\App\Services\PartsSupplierService::class)
        ->getPartDetails($part->supplier, $part->supplier_part_number);
    if ($details) {
        $part->update(['cost_price' => $details['price']]);
    }
}
```

## Troubleshooting

### No Suppliers Configured
**Error**: "No suppliers configured"

**Solution**: Add API keys to `.env`:
```env
EUROCARPARTS_API_KEY=your_key_here
```

### Search Returns Empty Results
**Possible Causes**:
1. API key invalid or expired
2. Supplier API down
3. Search query too specific

**Debug**:
```bash
# Check logs
tail -f storage/logs/laravel.log

# Test API directly
php artisan tinker
$service = app(\App\Services\PartsSupplierService::class);
$results = $service->searchParts('oil filter');
dd($results);
```

### Import Fails
**Error**: "Failed to import part"

**Check**:
1. Part number doesn't already exist
2. Required fields are populated
3. Database permissions

### Order Placement Fails
**Possible Causes**:
1. Invalid supplier credentials
2. Insufficient credit with supplier
3. Part out of stock
4. API endpoint changed

**Solution**: Check `storage/logs/laravel.log` for detailed error

## Alternative: Manual Parts Catalog

If you can't obtain supplier API access immediately, use the built-in manual catalog:

```bash
# Create parts manually
php artisan tinker
```

```php
Part::create([
    'part_number' => 'BP-FORD-001',
    'name' => 'Front Brake Pads - Ford Focus',
    'category' => 'Brakes',
    'manufacturer' => 'Brembo',
    'cost_price' => 45.99,
    'selling_price' => 65.99,
    'stock_quantity' => 4,
    'minimum_stock' => 2,
]);
```

## Next Steps

1. **Register with at least one supplier** (Euro Car Parts recommended)
2. **Add API credentials** to `.env`
3. **Test search** via admin panel
4. **Import common parts** to your catalog
5. **Set up markup percentages** per category
6. **Configure automatic price updates** (optional)

## Support

**Supplier API Issues**: Contact supplier support directly
- Euro Car Parts: trade@eurocarparts.com
- GSF: trade@gsfcarparts.com
- Auto Doc: b2b@autodoc.de
- Oscaro: Via account manager

**Integration Issues**: Check Laravel logs at `storage/logs/laravel.log`
