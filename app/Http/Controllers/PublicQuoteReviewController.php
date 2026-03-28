<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Mail\QuoteApproved;
use App\Mail\QuoteApprovedConfirmation;
use App\Mail\QuoteDeclined;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PublicQuoteReviewController extends Controller
{
    public function __construct(protected SmsService $smsService) {}

    /**
     * Show the quote review page (no login required — token-based).
     */
    public function show(string $token)
    {
        $quote = Quote::where('review_token', $token)
            ->with(['customer', 'vehicle', 'items', 'appointment'])
            ->firstOrFail();

        // Mark expired
        if ($quote->isExpired() && $quote->status === 'sent') {
            $quote->update(['status' => 'expired']);
        }

        return view('quotes.public-review', compact('quote', 'token'));
    }

    /**
     * Customer approves the quote via the public link.
     */
    public function approve(string $token)
    {
        $quote = Quote::where('review_token', $token)
            ->with(['customer', 'vehicle', 'appointment'])
            ->firstOrFail();

        if ($quote->status !== 'sent') {
            return redirect()->route('quote.review', $token)
                ->with('error', match($quote->status) {
                    'approved'  => 'This quote has already been approved.',
                    'declined'  => 'This quote has already been declined.',
                    'expired'   => 'This quote has expired. Please contact us for an updated quote.',
                    'converted' => 'This quote has already been converted to a booking.',
                    default     => 'This quote cannot be actioned.',
                });
        }

        if ($quote->isExpired()) {
            $quote->update(['status' => 'expired']);
            return redirect()->route('quote.review', $token)
                ->with('error', 'This quote has expired. Please contact us for an updated quote.');
        }

        $quote->approve();

        // If linked to an appointment, confirm it
        if ($quote->appointment) {
            $quote->appointment->update(['status' => 'confirmed']);

            try {
                Mail::to($quote->customer->email)
                    ->send(new \App\Mail\AppointmentConfirmation($quote->appointment));
            } catch (\Exception $e) {
                \Log::warning('Failed to send appointment confirmation after quote approval', ['error' => $e->getMessage()]);
            }
        }

        // Notify admin
        try {
            Mail::to(config('mail.from.address'))->send(new QuoteApproved($quote));
        } catch (\Exception $e) {
            \Log::warning('Failed to send quote approved admin email', ['error' => $e->getMessage()]);
        }

        // Send customer confirmation
        try {
            Mail::to($quote->customer->email)->send(new QuoteApprovedConfirmation($quote));
        } catch (\Exception $e) {
            \Log::warning('Failed to send quote approved confirmation email', ['error' => $e->getMessage()]);
        }

        // SMS notification
        if ($quote->customer->phone && $quote->customer->sms_notifications ?? false) {
            $message = "Your quote {$quote->quote_number} has been approved! We'll be in touch shortly. Total: £" . number_format($quote->total_amount, 2);
            $this->smsService->send($quote->customer->phone, $message);
        }

        return redirect()->route('quote.review', $token)
            ->with('success', 'Thank you! Your quote has been approved. We will be in touch shortly to confirm your booking.');
    }

    /**
     * Customer declines the quote via the public link.
     */
    public function decline(Request $request, string $token)
    {
        $request->validate([
            'decline_reason' => 'nullable|string|max:500',
        ]);

        $quote = Quote::where('review_token', $token)
            ->with(['customer', 'vehicle', 'appointment'])
            ->firstOrFail();

        if ($quote->status !== 'sent') {
            return redirect()->route('quote.review', $token)
                ->with('error', 'This quote cannot be declined at this stage.');
        }

        if ($quote->isExpired()) {
            $quote->update(['status' => 'expired']);
            return redirect()->route('quote.review', $token)
                ->with('error', 'This quote has expired. Please contact us for an updated quote.');
        }

        $quote->decline();

        // If linked to an appointment, mark it as pending again (so staff can follow up)
        if ($quote->appointment && $quote->appointment->status === 'pending_quote') {
            $quote->appointment->update(['status' => 'pending']);
        }

        // Notify admin
        try {
            Mail::to(config('mail.from.address'))->send(new QuoteDeclined($quote));
        } catch (\Exception $e) {
            \Log::warning('Failed to send quote declined admin email', ['error' => $e->getMessage()]);
        }

        return redirect()->route('quote.review', $token)
            ->with('success', 'Thank you for letting us know. Your quote has been declined. Please contact us if you have any questions.');
    }

    /**
     * Customer suggests an alternative date/time instead of outright declining.
     */
    public function suggestDate(Request $request, string $token)
    {
        $request->validate([
            'suggested_date' => 'required|date|after:today',
            'suggested_time' => 'required|string|max:10',
            'suggestion_notes' => 'nullable|string|max:500',
        ]);

        $quote = Quote::where('review_token', $token)
            ->with(['customer', 'vehicle', 'appointment'])
            ->firstOrFail();

        if ($quote->status !== 'sent') {
            return redirect()->route('quote.review', $token)
                ->with('error', 'This quote cannot be actioned at this stage.');
        }

        if ($quote->isExpired()) {
            $quote->update(['status' => 'expired']);
            return redirect()->route('quote.review', $token)
                ->with('error', 'This quote has expired. Please contact us for an updated quote.');
        }

        // Store the suggestion on the linked appointment
        if ($quote->appointment) {
            $quote->appointment->update([
                'reschedule_requested_date' => $request->suggested_date,
                'reschedule_requested_time' => $request->suggested_time,
                'reschedule_notes'          => $request->suggestion_notes,
                'status'                    => 'reschedule_pending',
            ]);
        }

        // Notify admin
        try {
            Mail::to(config('mail.from.address'))->send(new \App\Mail\QuoteDateSuggestion($quote, $request->suggested_date, $request->suggested_time, $request->suggestion_notes));
        } catch (\Exception $e) {
            \Log::warning('Failed to send quote date suggestion admin email', ['error' => $e->getMessage()]);
        }

        return redirect()->route('quote.review', $token)
            ->with('success', 'Thank you! Your preferred date has been sent to us. We will be in touch shortly to confirm the new time.');
    }
}
