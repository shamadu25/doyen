<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class QuoteController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        $quotes = $customer->quotes()
            ->with('vehicle')
            ->latest()
            ->paginate(20);
        
        return view('customer.quotes.index', compact('quotes'));
    }

    public function show(Quote $quote)
    {
        $customer = Auth::guard('customer')->user();
        
        if ($quote->customer_id !== $customer->id) {
            abort(403);
        }

        $quote->load(['vehicle', 'items']);

        return view('customer.quotes.show', compact('quote'));
    }

    public function approve(Quote $quote)
    {
        $customer = Auth::guard('customer')->user();
        
        if ($quote->customer_id !== $customer->id) {
            abort(403);
        }

        if ($quote->status !== 'sent') {
            return back()->withErrors(['error' => 'This quote cannot be approved.']);
        }

        if ($quote->isExpired()) {
            return back()->withErrors(['error' => 'This quote has expired. Please contact us for a new quote.']);
        }

        $quote->approve();

        // Notify admin
        \Mail::to(config('mail.from.address'))->send(
            new \App\Mail\QuoteApproved($quote)
        );

        // Send customer confirmation
        \Mail::to($customer->email)->send(
            new \App\Mail\QuoteApprovedConfirmation($quote)
        );

        // SMS notification
        if ($customer->phone && $customer->sms_notifications) {
            $message = "Your quote {$quote->quote_number} has been approved! We'll contact you shortly to schedule the work. Total: £" . number_format($quote->total_amount, 2);
            $this->smsService->send($customer->phone, $message);
        }

        return redirect()->route('customer.quotes.show', $quote)
            ->with('success', 'Quote approved successfully! We will contact you shortly to schedule the work.');
    }

    public function decline(Quote $quote)
    {
        $customer = Auth::guard('customer')->user();
        
        if ($quote->customer_id !== $customer->id) {
            abort(403);
        }

        if ($quote->status !== 'sent') {
            return back()->withErrors(['error' => 'This quote cannot be declined.']);
        }

        $quote->decline();

        // Notify admin
        \Mail::to(config('mail.from.address'))->send(
            new \App\Mail\QuoteDeclined($quote)
        );

        return redirect()->route('customer.quotes.index')
            ->with('success', 'Quote declined. Thank you for your time.');
    }
}
