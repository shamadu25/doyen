<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        $invoices = Invoice::whereHas('jobCard.vehicle', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })
        ->with(['jobCard.vehicle', 'payments'])
        ->latest()
        ->paginate(20);

        return view('customer.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $customer = Auth::guard('customer')->user();
        
        // Verify this invoice belongs to the customer
        if ($invoice->jobCard->vehicle->customer_id !== $customer->id) {
            abort(403, 'Unauthorized');
        }

        $invoice->load(['jobCard.vehicle', 'jobCard.services', 'jobCard.parts', 'payments']);

        return view('customer.invoices.show', compact('invoice'));
    }
}
