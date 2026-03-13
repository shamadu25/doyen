<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::query()
            ->with(['customer', 'vehicle'])
            ->when($request->search, function ($query, $search) {
                $query->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', fn($q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Invoices/Create', [
            'customers' => \App\Models\Customer::select('id', 'first_name', 'last_name')->get(),
            'vehicles' => \App\Models\Vehicle::select('id', 'customer_id', 'registration_number', 'make', 'model')->get(),
            'defaultVatRate' => (float) Setting::get('vat_rate', 20),
            'defaultTerms' => Setting::get('invoice_terms', 'Payment due within 30 days.'),
        ]);
    }

    public function store(InvoiceRequest $request)
    {
        $items = $request->input('items', []);
        $vatRate = (float) Setting::get('vat_rate', 20);
        $subtotal = 0;
        $vatTotal = 0;
        $discountTotal = 0;

        foreach ($items as $item) {
            $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
            $itemVat = $lineTotal * (($item['vat_rate'] ?? $vatRate) / 100);
            $subtotal += $lineTotal;
            $vatTotal += $itemVat;
            $discountTotal += ($item['discount'] ?? 0);
        }

        $invoice = Invoice::create([
            'customer_id' => $request->customer_id,
            'vehicle_id' => $request->vehicle_id,
            'job_card_id' => $request->job_card_id,
            'invoice_date' => now(),
            'due_date' => $request->due_date ?? now()->addDays(30),
            'subtotal' => round($subtotal, 2),
            'vat_amount' => round($vatTotal, 2),
            'total_amount' => round($subtotal + $vatTotal, 2),
            'discount_amount' => round($discountTotal, 2),
            'paid_amount' => 0,
            'status' => 'draft',
            'notes' => $request->notes,
            'terms' => $request->terms ?? Setting::get('invoice_terms'),
        ]);

        foreach ($items as $item) {
            $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
            $itemVat = $lineTotal * (($item['vat_rate'] ?? $vatRate) / 100);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => $item['type'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $item['vat_rate'] ?? $vatRate,
                'discount' => $item['discount'] ?? 0,
                'line_total' => round($lineTotal, 2),
                'vat_amount' => round($itemVat, 2),
            ]);
        }

        ActivityLog::log('created', "Invoice {$invoice->invoice_number} created", $invoice);
        return redirect()->route('invoices.show', $invoice)->with('success', "Invoice {$invoice->invoice_number} created.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'vehicle', 'jobCard', 'items', 'payments']);
        $garageSettings = Setting::getAllSettings();

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
            'garageInfo' => [
                'name' => $garageSettings['garage_name'] ?? 'Doyen Auto Services',
                'address' => $garageSettings['address'] ?? '',
                'city' => $garageSettings['city'] ?? '',
                'postcode' => $garageSettings['postcode'] ?? '',
                'phone' => $garageSettings['phone'] ?? '',
                'email' => $garageSettings['email'] ?? '',
                'vat_number' => $garageSettings['vat_number'] ?? '',
            ],
        ]);
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');
        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice,
            'customers' => \App\Models\Customer::select('id', 'first_name', 'last_name')->get(),
            'vehicles' => \App\Models\Vehicle::select('id', 'customer_id', 'registration_number', 'make', 'model')->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $invoice->update($request->only('notes', 'terms', 'due_date', 'status'));
        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->items()->delete();
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }

    public function send(Invoice $invoice)
    {
        $invoice->update(['status' => 'sent']);
        // Queue email notification
        if ($invoice->customer?->email) {
            \Illuminate\Support\Facades\Mail::to($invoice->customer->email)
                ->queue(new \App\Mail\InvoiceCreated($invoice));
        }
        ActivityLog::log('sent', "Invoice {$invoice->invoice_number} sent to customer", $invoice);
        return back()->with('success', 'Invoice sent to customer.');
    }

    public function markPaid(Invoice $invoice)
    {
        $invoice->update([
            'status' => 'paid',
            'paid_amount' => $invoice->total_amount,
            'paid_date' => now(),
        ]);
        ActivityLog::log('paid', "Invoice {$invoice->invoice_number} marked as paid", $invoice);
        return back()->with('success', 'Invoice marked as paid.');
    }

    public function download(Invoice $invoice)
    {
        $invoice->load(['customer', 'vehicle', 'items']);
        $garageSettings = Setting::getAllSettings();

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'garage' => $garageSettings,
        ]);

        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    public function creditNote(Invoice $invoice)
    {
        $creditNote = $invoice->replicate();
        $creditNote->invoice_number = 'CN-' . $invoice->invoice_number;
        $creditNote->total_amount = -$invoice->total_amount;
        $creditNote->subtotal = -$invoice->subtotal;
        $creditNote->vat_amount = -$invoice->vat_amount;
        $creditNote->status = 'refunded';
        $creditNote->notes = "Credit note for Invoice {$invoice->invoice_number}";
        $creditNote->save();

        foreach ($invoice->items as $item) {
            $creditItem = $item->replicate();
            $creditItem->invoice_id = $creditNote->id;
            $creditItem->unit_price = -$item->unit_price;
            $creditItem->save();
        }

        $invoice->update(['status' => 'refunded']);

        ActivityLog::log('credit_note', "Credit note {$creditNote->invoice_number} created", $creditNote);
        return redirect()->route('invoices.show', $creditNote)->with('success', 'Credit note created.');
    }
}
