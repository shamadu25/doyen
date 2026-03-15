<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Service;
use App\Models\Part;
use App\Services\SmsService;
use App\Mail\QuoteCreated;
use App\Mail\QuoteReviewRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Inertia\Inertia;

class QuoteController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index(Request $request)
    {
        $query = Quote::with(['customer', 'vehicle']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $quotes = $query->orderByDesc('quote_date')->paginate(20);

        return Inertia::render('Quotes/Index', ['quotes' => $quotes]);
    }

    public function create()
    {
        $customers = Customer::select('id', 'first_name', 'last_name')->orderBy('first_name')->get()->map(fn($c) => ['id' => $c->id, 'name' => $c->first_name . ' ' . $c->last_name]);
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $parts = Part::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Quotes/Create', ['customers' => $customers, 'services' => $services, 'parts' => $parts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'quote_date' => 'required|date',
            'validity_days' => 'required|integer|min:1|max:365',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:service,part,labour',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $quote = Quote::create([
            'customer_id' => $validated['customer_id'],
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'quote_date' => $validated['quote_date'],
            'validity_days' => $validated['validity_days'],
            'valid_until' => Carbon::parse($validated['quote_date'])->addDays($validated['validity_days']),
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'discount_percentage' => $validated['discount_percentage'] ?? 0,
            'status' => 'draft',
        ]);

        foreach ($validated['items'] as $itemData) {
            QuoteItem::create([
                'quote_id' => $quote->id,
                'item_type' => $itemData['item_type'],
                'service_id' => $itemData['service_id'] ?? null,
                'part_id' => $itemData['part_id'] ?? null,
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
            ]);
        }

        $quote->calculateTotals();

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quote created successfully.');
    }

    public function show(Quote $quote)
    {
        $quote->load(['customer', 'vehicle', 'items.service', 'items.part']);

        return Inertia::render('Quotes/Show', ['quote' => $quote]);
    }

    public function edit(Quote $quote)
    {
        if (in_array($quote->status, ['approved', 'converted'])) {
            return back()->with('error', 'Cannot edit an approved or converted quote.');
        }

        $quote->load('items');
        $customers = Customer::select('id', 'first_name', 'last_name')->orderBy('first_name')->get()->map(fn($c) => ['id' => $c->id, 'name' => $c->first_name . ' ' . $c->last_name]);
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $parts = Part::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Quotes/Edit', ['quote' => $quote, 'customers' => $customers, 'services' => $services, 'parts' => $parts]);
    }

    public function update(Request $request, Quote $quote)
    {
        if (in_array($quote->status, ['approved', 'converted'])) {
            return back()->with('error', 'Cannot edit an approved or converted quote.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'quote_date' => 'required|date',
            'validity_days' => 'required|integer|min:1|max:365',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $quote->update($validated);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quote updated successfully.');
    }

    public function destroy(Quote $quote)
    {
        if ($quote->status === 'converted') {
            return back()->with('error', 'Cannot delete a converted quote.');
        }

        $quote->delete();

        return redirect()->route('quotes.index')
            ->with('success', 'Quote deleted successfully.');
    }

    /**
     * Send quote to customer — delegates to sendForReview so the customer
     * always receives the secure approve/decline link.
     */
    public function send(Quote $quote)
    {
        return $this->sendForReview($quote);
    }

    /**
     * Send quote to customer for review and approval via a secure token link.
     */
    public function sendForReview(Quote $quote)
    {
        if (in_array($quote->status, ['approved', 'converted'])) {
            return back()->with('error', 'Cannot send an approved or converted quote for review.');
        }

        if (! $quote->review_token) {
            $quote->generateReviewToken();
            $quote->refresh();
        }

        $quote->update(['status' => 'sent']);

        $reviewUrl = route('quote.review', $quote->review_token);

        try {
            Mail::to($quote->customer->email)->send(new QuoteReviewRequest($quote, $reviewUrl));
        } catch (\Exception $e) {
            \Log::warning('Failed to send quote review request email', ['error' => $e->getMessage()]);
        }

        if ($this->smsService->isEnabled()) {
            $this->smsService->sendQuoteNotification($quote);
        }

        return back()->with('success', 'Quote sent to customer for review and approval.');
    }

    /**
     * Approve quote
     */
    public function approve(Quote $quote)
    {
        $quote->approve();

        return back()->with('success', 'Quote approved successfully.');
    }

    /**
     * Decline quote
     */
    public function decline(Quote $quote)
    {
        $quote->decline();

        return back()->with('success', 'Quote declined.');
    }

    /**
     * Convert quote to job card
     */
    public function convert(Quote $quote)
    {
        if ($quote->status !== 'approved') {
            $quote->approve();
        }

        $jobCard = $quote->convertToJobCard();

        return redirect()->route('job-cards.show', $jobCard)
            ->with('success', "Quote converted to Job Card #{$jobCard->job_number}");
    }

    /**
     * Get quote items for AJAX
     */
    public function getItems(Quote $quote)
    {
        return response()->json([
            'items' => $quote->items->load(['service', 'part']),
        ]);
    }
}
