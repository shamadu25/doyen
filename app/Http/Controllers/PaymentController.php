<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Mail\AdminPaymentAlert;
use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::query()
            ->with(['invoice', 'customer'])
            ->when($request->search, function ($query, $search) {
                $query->where('payment_reference', 'like', "%{$search}%")
                      ->orWhereHas('invoice', fn($q) => $q->where('invoice_number', 'like', "%{$search}%"))
                      ->orWhereHas('customer', fn($q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            })
            ->when($request->method, fn($q, $m) => $q->where('payment_method', $m))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest('payment_date')
            ->paginate(15)
            ->withQueryString();

        $totalReceived = Payment::where('status', 'completed')->sum('amount');

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only('search', 'method', 'status'),
            'totalReceived' => round((float) $totalReceived, 2),
        ]);
    }

    public function create(Request $request)
    {
        $invoices = Invoice::whereIn('status', ['sent', 'partial', 'draft', 'overdue'])
            ->with('customer')
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'customer_name' => $inv->customer?->full_name,
                'total_amount' => $inv->total_amount,
                'paid_amount' => $inv->paid_amount,
                'balance' => $inv->total_amount - $inv->paid_amount,
            ]);

        $customers = \App\Models\Customer::select('id', 'first_name', 'last_name')
            ->orderBy('first_name')->get()
            ->map(fn($c) => ['id' => $c->id, 'first_name' => $c->first_name, 'last_name' => $c->last_name]);

        return Inertia::render('Payments/Create', [
            'invoices' => $invoices,
            'customers' => $customers,
            'preselectedInvoiceId' => $request->invoice_id,
        ]);
    }

    public function store(PaymentRequest $request)
    {
        $invoice = Invoice::findOrFail($request->invoice_id);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'payment_date' => now(),
            'status' => 'completed',
            'notes' => $request->notes,
        ]);

        // Update invoice
        $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
        $invoice->update([
            'paid_amount' => $totalPaid,
            'status' => $totalPaid >= $invoice->total_amount ? 'paid' : 'partial',
            'paid_date' => $totalPaid >= $invoice->total_amount ? now() : null,
        ]);

        ActivityLog::log('payment', "Payment of £{$payment->amount} received for invoice {$invoice->invoice_number}", $payment);

        // Notify admin via email and SMS
        $invoice->refresh()->load('customer');
        try {
            $adminEmail = env('ADMIN_EMAIL', env('GARAGE_EMAIL'));
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new AdminPaymentAlert($payment, $invoice));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send admin payment alert email', ['error' => $e->getMessage()]);
        }
        try {
            (new SmsService())->sendAdminPaymentAlert($payment, $invoice);
        } catch (\Exception $e) {
            \Log::warning('Failed to send admin payment alert SMS', ['error' => $e->getMessage()]);
        }

        return redirect()->route('payments.index')->with('success', "Payment of £{$payment->amount} recorded.");
    }

    public function show(Payment $payment)
    {
        $payment->load(['invoice.customer', 'customer']);
        return Inertia::render('Payments/Show', ['payment' => $payment]);
    }

    public function stripePayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.50',
        ]);

        $invoice = Invoice::with('customer')->findOrFail($request->invoice_id);

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int) ($request->amount * 100), // pence
                'currency' => 'gbp',
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->customer?->full_name,
                ],
                'description' => "Payment for Invoice {$invoice->invoice_number}",
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig,
                config('services.stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            return response('Invalid webhook', 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;
            $invoiceId = $intent->metadata->invoice_id ?? null;

            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    $stripePayment = Payment::create([
                        'invoice_id' => $invoice->id,
                        'customer_id' => $invoice->customer_id,
                        'amount' => $intent->amount / 100,
                        'payment_method' => 'stripe',
                        'payment_reference' => $intent->id,
                        'payment_date' => now(),
                        'status' => 'completed',
                        'notes' => 'Stripe payment',
                    ]);

                    $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
                    $invoice->update([
                        'paid_amount' => $totalPaid,
                        'status' => $totalPaid >= $invoice->total_amount ? 'paid' : 'partial',
                        'paid_date' => $totalPaid >= $invoice->total_amount ? now() : null,
                    ]);

                    // Notify admin of Stripe payment
                    $invoice->refresh()->load('customer');
                    try {
                        $adminEmail = env('ADMIN_EMAIL', env('GARAGE_EMAIL'));
                        if ($adminEmail) {
                            Mail::to($adminEmail)->send(new AdminPaymentAlert($stripePayment, $invoice));
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Admin Stripe payment alert email failed', ['error' => $e->getMessage()]);
                    }
                    try {
                        (new SmsService())->sendAdminPaymentAlert($stripePayment, $invoice);
                    } catch (\Exception $e) {
                        \Log::warning('Admin Stripe payment alert SMS failed', ['error' => $e->getMessage()]);
                    }
                }
            }
        }

        return response('OK', 200);
    }

    public function refund(Payment $payment)
    {
        if ($payment->payment_method === 'stripe' && $payment->payment_reference) {
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                \Stripe\Refund::create(['payment_intent' => $payment->payment_reference]);
            } catch (\Exception $e) {
                return back()->with('error', 'Stripe refund failed: ' . $e->getMessage());
            }
        }

        $payment->update(['status' => 'refunded']);

        $invoice = $payment->invoice;
        if ($invoice) {
            $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
            $invoice->update([
                'paid_amount' => $totalPaid,
                'status' => $totalPaid <= 0 ? 'refunded' : ($totalPaid >= $invoice->total_amount ? 'paid' : 'partial'),
            ]);
        }

        ActivityLog::log('refund', "Payment refunded: £{$payment->amount}", $payment);
        return back()->with('success', "Payment of £{$payment->amount} refunded.");
    }
}
