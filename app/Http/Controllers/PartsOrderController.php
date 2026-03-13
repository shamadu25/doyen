<?php

namespace App\Http\Controllers;

use App\Models\PartsOrder;
use App\Models\PartsOrderItem;
use App\Models\JobCard;
use App\Services\EuroCarPartsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartsOrderController extends Controller
{
    public function __construct(
        protected EuroCarPartsService $euroCarParts
    ) {}

    /**
     * Display parts orders list
     */
    public function index(Request $request)
    {
        $query = PartsOrder::with(['jobCard.vehicle', 'user', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', $request->supplier);
        }

        $orders = $query->latest()->paginate(20);

        return view('parts-orders.index', compact('orders'));
    }

    /**
     * Show parts search interface
     */
    public function search(Request $request)
    {
        $jobCard = null;
        if ($request->filled('job_card_id')) {
            $jobCard = JobCard::with('vehicle')->findOrFail($request->job_card_id);
        }

        $categories = $this->euroCarParts->getCategories();

        return view('parts-orders.search', compact('jobCard', 'categories'));
    }

    /**
     * Search parts via Euro Car Parts API
     */
    public function apiSearch(Request $request)
    {
        $request->validate([
            'search_type' => 'required|in:registration,vin,description',
            'search_value' => 'required|string',
        ]);

        $results = match($request->search_type) {
            'registration' => $this->euroCarParts->searchByRegistration(
                $request->search_value,
                $request->part_type
            ),
            'vin' => $this->euroCarParts->searchByVin(
                $request->search_value,
                $request->part_type
            ),
            'description' => $this->euroCarParts->searchByDescription(
                $request->search_value,
                $request->make,
                $request->model
            ),
        };

        return response()->json($results);
    }

    /**
     * Get part details and pricing
     */
    public function getPartDetails(Request $request)
    {
        $request->validate([
            'part_number' => 'required|string',
            'quantity' => 'integer|min:1',
        ]);

        $details = $this->euroCarParts->getPartDetails($request->part_number);
        $pricing = $this->euroCarParts->getPricing($request->part_number, $request->quantity ?? 1);
        $stock = $this->euroCarParts->checkStock($request->part_number, $request->quantity ?? 1);

        return response()->json([
            'details' => $details,
            'pricing' => $pricing,
            'stock' => $stock,
        ]);
    }

    /**
     * Create new parts order
     */
    public function create(Request $request)
    {
        $jobCard = null;
        if ($request->filled('job_card_id')) {
            $jobCard = JobCard::with('vehicle', 'customer')->findOrFail($request->job_card_id);
        }

        return view('parts-orders.create', compact('jobCard'));
    }

    /**
     * Store new parts order
     */
    public function store(Request $request)
    {
        $request->validate([
            'job_card_id' => 'nullable|exists:job_cards,id',
            'supplier' => 'required|in:eurocarparts,gsf,autodoc,oscaro',
            'delivery_method' => 'required|string',
            'delivery_address' => 'nullable|string',
            'expected_delivery_date' => 'nullable|date',
            'parts' => 'required|array|min:1',
            'parts.*.part_number' => 'required|string',
            'parts.*.part_name' => 'required|string',
            'parts.*.quantity' => 'required|integer|min:1',
            'parts.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = collect($request->parts)->sum(function($part) {
                return $part['quantity'] * $part['unit_price'];
            });
            $vat = $subtotal * 0.20;
            $deliveryCost = $request->delivery_cost ?? 0;
            $total = $subtotal + $vat + $deliveryCost;

            // Create order
            $order = PartsOrder::create([
                'job_card_id' => $request->job_card_id,
                'user_id' => auth()->id(),
                'supplier' => $request->supplier,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'vat' => $vat,
                'delivery_cost' => $deliveryCost,
                'total' => $total,
                'delivery_method' => $request->delivery_method,
                'expected_delivery_date' => $request->expected_delivery_date,
                'delivery_address' => $request->delivery_address,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($request->parts as $part) {
                PartsOrderItem::create([
                    'parts_order_id' => $order->id,
                    'part_number' => $part['part_number'],
                    'supplier_part_number' => $part['supplier_part_number'] ?? null,
                    'part_name' => $part['part_name'],
                    'description' => $part['description'] ?? null,
                    'manufacturer' => $part['manufacturer'] ?? null,
                    'quantity' => $part['quantity'],
                    'unit_price' => $part['unit_price'],
                    'discount' => $part['discount'] ?? 0,
                ]);
            }

            // If using Euro Car Parts, submit order to API
            if ($request->supplier === 'eurocarparts' && $this->euroCarParts->isEnabled()) {
                $apiResponse = $this->euroCarParts->placeOrder([
                    'reference' => $order->order_number,
                    'delivery_address' => $request->delivery_address,
                    'delivery_method' => $request->delivery_method,
                    'parts' => collect($request->parts)->map(function($part) {
                        return [
                            'part_number' => $part['part_number'],
                            'quantity' => $part['quantity'],
                        ];
                    })->toArray(),
                ]);

                if (!isset($apiResponse['error'])) {
                    $order->update([
                        'supplier_order_reference' => $apiResponse['order_reference'] ?? null,
                        'status' => 'confirmed',
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('parts-orders.show', $order)
                ->with('success', 'Parts order created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Display order details
     */
    public function show(PartsOrder $partsOrder)
    {
        $partsOrder->load(['jobCard.vehicle.customer', 'user', 'items']);

        // Get latest status from supplier if order is confirmed
        if ($partsOrder->status === 'confirmed' && $partsOrder->supplier_order_reference) {
            $status = $this->euroCarParts->getOrderStatus($partsOrder->supplier_order_reference);
            
            if (!isset($status['error'])) {
                // Update order status if changed
                if (isset($status['status'])) {
                    $partsOrder->update(['status' => $status['status']]);
                }
            }
        }

        return view('parts-orders.show', compact('partsOrder'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, PartsOrder $partsOrder)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string',
            'supplier_order_reference' => 'nullable|string',
        ]);

        $partsOrder->update([
            'status' => $request->status,
            'tracking_number' => $request->tracking_number ?? $partsOrder->tracking_number,
            'supplier_order_reference' => $request->supplier_order_reference ?? $partsOrder->supplier_order_reference,
        ]);

        if ($request->status === 'delivered') {
            $partsOrder->update(['actual_delivery_date' => now()]);
        }

        return back()->with('success', 'Order status updated successfully!');
    }

    /**
     * Check delivery options
     */
    public function checkDelivery(Request $request)
    {
        $request->validate([
            'postcode' => 'required|string',
            'parts' => 'required|array',
        ]);

        $options = $this->euroCarParts->getDeliveryOptions(
            $request->postcode,
            $request->parts
        );

        return response()->json($options);
    }

    /**
     * Add parts to job card from order
     */
    public function addToJobCard(Request $request, PartsOrder $partsOrder)
    {
        if (!$partsOrder->job_card_id) {
            return back()->with('error', 'No job card associated with this order');
        }

        DB::beginTransaction();
        try {
            foreach ($partsOrder->items as $item) {
                // Add part to job card
                \App\Models\JobCardPart::create([
                    'job_card_id' => $partsOrder->job_card_id,
                    'part_id' => null, // External part
                    'part_name' => $item->part_name,
                    'part_number' => $item->part_number,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price * 1.3, // 30% markup
                    'discount' => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('job-cards.show', $partsOrder->job_card_id)
                ->with('success', 'Parts added to job card successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add parts: ' . $e->getMessage());
        }
    }
}
