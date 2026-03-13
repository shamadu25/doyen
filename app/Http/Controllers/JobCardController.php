<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobCardRequest;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\InventoryTransaction;
use App\Models\JobCard;
use App\Models\JobCardPart;
use App\Models\JobCardService;
use App\Models\Part;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobCardController extends Controller
{
    public function index(Request $request)
    {
        $jobCards = JobCard::query()
            ->with(['customer', 'vehicle', 'assignedTo'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('job_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', fn($cq) => $cq->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                      ->orWhereHas('vehicle', fn($vq) => $vq->where('registration_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->priority, fn($q, $p) => $q->where('priority', $p))
            ->when($request->technician, fn($q, $t) => $q->where('assigned_to', $t))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('JobCards/Index', [
            'jobCards' => $jobCards,
            'filters' => $request->only('search', 'status', 'priority', 'technician'),
            'technicians' => User::where('role', 'technician')->where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('JobCards/Create', [
            'customers' => Customer::select('id', 'first_name', 'last_name')->orderBy('first_name')->get(),
            'vehicles' => Vehicle::select('id', 'customer_id', 'registration_number', 'make', 'model')->get(),
            'technicians' => User::where('role', 'technician')->where('is_active', true)->get(['id', 'name']),
            'preselectedCustomerId' => $request->customer_id,
            'preselectedVehicleId' => $request->vehicle_id,
        ]);
    }

    public function store(JobCardRequest $request)
    {
        $jobCard = JobCard::create(array_merge($request->validated(), [
            'status' => 'pending',
            'date_in' => now(),
        ]));

        ActivityLog::log('created', "Job Card {$jobCard->job_number} created", $jobCard);
        return redirect()->route('job-cards.show', $jobCard)->with('success', "Job Card {$jobCard->job_number} created.");
    }

    public function show(JobCard $jobCard)
    {
        $jobCard->load([
            'customer', 'vehicle', 'assignedTo', 'appointment',
            'services.service', 'parts.part', 'invoice',
            'documents.uploader',
        ]);

        $labourTotal = $jobCard->services->sum(fn($s) => ($s->unit_price * $s->quantity) - ($s->discount ?? 0));
        $partsTotal = $jobCard->parts->sum(fn($p) => ($p->unit_price * $p->quantity) - ($p->discount ?? 0));
        $vatRate = (float) Setting::get('vat_rate', 20);
        $vatTotal = ($labourTotal + $partsTotal) * ($vatRate / 100);
        $grandTotal = $labourTotal + $partsTotal + $vatTotal;

        return Inertia::render('JobCards/Show', [
            'jobCard' => $jobCard,
            'totals' => [
                'labour' => round($labourTotal, 2),
                'parts' => round($partsTotal, 2),
                'vat' => round($vatTotal, 2),
                'grand_total' => round($grandTotal, 2),
                'vat_rate' => $vatRate,
            ],
            'availableServices' => Service::where('is_active', true)->get(),
            'availableParts' => Part::where('is_active', true)->where('stock_quantity', '>', 0)->get(),
            'documentTypes' => [
                'diagnostic_report'   => 'Diagnostic Report',
                'health_check_report' => 'Health Check Report',
                'inspection_sheet'    => 'Inspection Sheet',
                'estimate'            => 'Estimate / Quote',
                'photo'               => 'Photo / Image',
                'other'               => 'Other',
            ],
        ]);
    }

    public function edit(JobCard $jobCard)
    {
        return Inertia::render('JobCards/Edit', [
            'jobCard' => $jobCard->load(['customer', 'vehicle']),
            'customers' => Customer::select('id', 'first_name', 'last_name')->get(),
            'vehicles' => Vehicle::select('id', 'customer_id', 'registration_number', 'make', 'model')->get(),
            'technicians' => User::where('role', 'technician')->where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function update(JobCardRequest $request, JobCard $jobCard)
    {
        $jobCard->update($request->validated());
        return redirect()->route('job-cards.show', $jobCard)->with('success', 'Job Card updated.');
    }

    public function destroy(JobCard $jobCard)
    {
        $jobCard->delete();
        return redirect()->route('job-cards.index')->with('success', 'Job Card deleted.');
    }

    public function addLabour(Request $request, JobCard $jobCard)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:500',
            'hours' => 'required|numeric|min:0.1',
            'rate' => 'required|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $vatRate = $validated['vat_rate'] ?? (float) Setting::get('vat_rate', 20);
        $lineTotal = $validated['hours'] * $validated['rate'];

        JobCardService::create([
            'job_card_id' => $jobCard->id,
            'description' => $validated['description'],
            'quantity' => $validated['hours'],
            'unit_price' => $validated['rate'],
            'vat_rate' => $vatRate,
            'discount' => 0,
        ]);

        return back()->with('success', 'Labour line added.');
    }

    public function addPart(Request $request, JobCard $jobCard)
    {
        $validated = $request->validate([
            'part_id'     => 'nullable|exists:parts,id',
            'description' => 'nullable|string|max:500',
            'quantity'    => 'required|numeric|min:1',
            'unit_price'  => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'vat_rate'    => 'nullable|numeric|min:0|max:100',
        ]);

        $vatRate = $validated['vat_rate'] ?? (float) Setting::get('vat_rate', 20);

        // Derive part name from inventory if not manually provided
        $partName = $validated['description'] ?? null;
        if (!$partName && !empty($validated['part_id'])) {
            $part = Part::find($validated['part_id']);
            $partName = $part?->name ?? 'Part';
        }
        if (!$partName) {
            $partName = 'Part';
        }

        JobCardPart::create([
            'job_card_id' => $jobCard->id,
            'part_id'     => $validated['part_id'] ?? null,
            'part_name'   => $partName,
            'quantity'    => $validated['quantity'],
            'unit_price'  => $validated['unit_price'],
            'unit_cost'   => $validated['cost_price'] ?? 0,
            'vat_rate'    => $vatRate,
            'discount'    => 0,
            'status'      => 'added',
        ]);

        // Deduct from inventory if part selected
        if (!empty($validated['part_id'])) {
            $part = $part ?? Part::find($validated['part_id']);
            if ($part) {
                InventoryTransaction::recordOut($part, $validated['quantity'], "Used in job {$jobCard->job_number}", $jobCard);
            }
        }

        return back()->with('success', 'Part added to job card.');
    }

    public function complete(JobCard $jobCard)
    {
        $jobCard->update([
            'status' => 'completed',
            'completion_date' => now(),
            'date_out' => now(),
        ]);

        ActivityLog::log('completed', "Job Card {$jobCard->job_number} completed", $jobCard);
        return back()->with('success', 'Job Card marked as completed.');
    }

    public function generateInvoice(JobCard $jobCard)
    {
        $jobCard->load(['services', 'parts', 'customer', 'vehicle']);

        $vatRate = (float) Setting::get('vat_rate', 20);
        $items = [];
        $subtotal = 0;

        // Add labour items
        foreach ($jobCard->services as $service) {
            $lineTotal = ($service->unit_price * $service->quantity) - ($service->discount ?? 0);
            $subtotal += $lineTotal;
            $items[] = [
                'item_type' => 'labour',
                'description' => $service->description ?? ($service->service?->name ?? 'Labour'),
                'quantity' => $service->quantity,
                'unit_price' => $service->unit_price,
                'vat_rate' => $vatRate,
                'discount' => $service->discount ?? 0,
            ];
        }

        // Add parts
        foreach ($jobCard->parts as $part) {
            $lineTotal = ($part->unit_price * $part->quantity) - ($part->discount ?? 0);
            $subtotal += $lineTotal;
            $items[] = [
                'item_type' => 'part',
                'description' => $part->description ?? ($part->part?->name ?? 'Part'),
                'quantity' => $part->quantity,
                'unit_price' => $part->unit_price,
                'vat_rate' => $vatRate,
                'discount' => $part->discount ?? 0,
            ];
        }

        $vatTotal = $subtotal * ($vatRate / 100);

        $invoice = \App\Models\Invoice::create([
            'customer_id' => $jobCard->customer_id,
            'vehicle_id' => $jobCard->vehicle_id,
            'job_card_id' => $jobCard->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => round($subtotal, 2),
            'vat_amount' => round($vatTotal, 2),
            'total_amount' => round($subtotal + $vatTotal, 2),
            'discount_amount' => 0,
            'paid_amount' => 0,
            'status' => 'draft',
        ]);

        foreach ($items as $item) {
            $lineTotal = ($item['quantity'] * $item['unit_price']) - $item['discount'];
            $vatAmount = $lineTotal * ($item['vat_rate'] / 100);
            \App\Models\InvoiceItem::create(array_merge($item, [
                'invoice_id' => $invoice->id,
                'line_total' => round($lineTotal, 2),
                'vat_amount' => round($vatAmount, 2),
            ]));
        }

        $jobCard->update(['status' => 'invoiced']);
        ActivityLog::log('invoiced', "Invoice {$invoice->invoice_number} generated from Job Card {$jobCard->job_number}", $invoice);

        return redirect()->route('invoices.show', $invoice)->with('success', "Invoice {$invoice->invoice_number} created.");
    }
}
