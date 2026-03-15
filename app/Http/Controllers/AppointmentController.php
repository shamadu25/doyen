<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmation;
use App\Mail\AppointmentRescheduled;
use App\Mail\QuoteReviewRequest;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Appointment::query()
            ->with(['customer', 'vehicle', 'assignedTo'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', fn($cq) => $cq->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                      ->orWhereHas('vehicle', fn($vq) => $vq->where('registration_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->date, fn($q, $date) => $q->whereDate('scheduled_date', $date))
            ->when($request->appointment_type, fn($q, $type) => $q->where('appointment_type', $type))
            ->latest('scheduled_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only('search', 'status', 'date', 'appointment_type'),
        ]);
    }

    public function calendar(Request $request)
    {
        $start = $request->get('start', now()->startOfWeek()->toDateString());
        $end = $request->get('end', now()->endOfWeek()->toDateString());

        $bookings = Appointment::with(['customer', 'vehicle', 'assignedTo'])
            ->whereBetween('scheduled_date', [$start, $end])
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'reference' => $b->reference_number,
                'customer_name' => $b->customer?->full_name ?? '',
                'vehicle_registration' => $b->vehicle?->registration_number ?? '',
                'appointment_type' => $b->appointment_type,
                'scheduled_date' => $b->scheduled_date?->format('Y-m-d'),
                'scheduled_time' => $b->scheduled_date?->format('H:i'),
                'duration_minutes' => $b->duration_minutes,
                'status' => $b->status,
                'assigned_to' => $b->assigned_to,
                'technician_name' => $b->assignedTo?->name,
            ]);

        $technicians = User::where('role', 'technician')->where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Bookings/Calendar', [
            'events' => $bookings,
            'technicians' => $technicians,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Bookings/Create', [
            'customers' => Customer::select('id', 'first_name', 'last_name')->orderBy('first_name')->get(),
            'vehicles' => Vehicle::select('id', 'customer_id', 'registration_number', 'make', 'model')->get(),
            'technicians' => User::where('role', 'technician')->where('is_active', true)->get(['id', 'name']),
            'preselectedCustomerId' => $request->customer_id,
            'preselectedVehicleId' => $request->vehicle_id,
        ]);
    }

    public function store(BookingRequest $request)
    {
        $booking = Appointment::create(array_merge($request->validated(), [
            'status' => 'pending',
        ]));

        ActivityLog::log('created', "Booking {$booking->reference_number} created", $booking);
        return redirect()->route('bookings.show', $booking)->with('success', 'Booking created successfully.');
    }

    public function show(Appointment $booking)
    {
        $booking->load(['customer', 'vehicle', 'assignedTo', 'jobCard', 'quote']);
        return Inertia::render('Bookings/Show', ['booking' => $booking]);
    }

    public function edit(Appointment $booking)
    {
        return Inertia::render('Bookings/Edit', [
            'booking' => $booking,
            'customers' => Customer::select('id', 'first_name', 'last_name')->get(),
            'vehicles' => Vehicle::select('id', 'customer_id', 'registration_number', 'make', 'model')->get(),
            'technicians' => User::where('role', 'technician')->where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function update(BookingRequest $request, Appointment $booking)
    {
        $booking->update($request->validated());
        return redirect()->route('bookings.show', $booking)->with('success', 'Booking updated successfully.');
    }

    public function destroy(Appointment $booking)
    {
        $booking->delete();
        return redirect()->route('bookings.index')->with('success', 'Booking deleted successfully.');
    }

    public function confirm(Appointment $booking)
    {
        $booking->update(['status' => 'confirmed']);
        ActivityLog::log('confirmed', "Booking {$booking->reference_number} confirmed", $booking);

        try {
            $booking->load(['customer', 'vehicle']);
            Mail::to($booking->customer->email)->send(new AppointmentConfirmation($booking));
        } catch (\Exception $e) {
            \Log::warning('Failed to send appointment confirmation email', [
                'appointment_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Booking confirmed and customer notified.');
    }

    public function cancel(Appointment $booking)
    {
        $booking->update(['status' => 'cancelled']);
        ActivityLog::log('cancelled', "Booking {$booking->reference_number} cancelled", $booking);

        try {
            $booking->load(['customer', 'vehicle']);
            Mail::to($booking->customer->email)->send(new AppointmentCancelled($booking));
        } catch (\Exception $e) {
            \Log::warning('Failed to send appointment cancellation email', [
                'appointment_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Booking cancelled and customer notified.');
    }

    public function complete(Appointment $booking)
    {
        $booking->update(['status' => 'completed']);
        ActivityLog::log('completed', "Booking {$booking->reference_number} completed", $booking);
        return back()->with('success', 'Booking marked as completed.');
    }

    public function convertToJob(Appointment $booking)
    {
        $jobCard = \App\Models\JobCard::create([
            'customer_id' => $booking->customer_id,
            'vehicle_id' => $booking->vehicle_id,
            'appointment_id' => $booking->id,
            'assigned_to' => $booking->assigned_to,
            'status' => 'pending',
            'priority' => 'normal',
            'mileage_in' => $booking->vehicle?->mileage,
            'date_in' => now(),
            'work_required' => $booking->notes,
        ]);

        $booking->update(['status' => 'in_progress']);
        ActivityLog::log('converted', "Booking {$booking->reference_number} converted to Job Card {$jobCard->job_number}", $jobCard);

        return redirect()->route('job-cards.show', $jobCard)->with('success', "Job Card {$jobCard->job_number} created from booking.");
    }

    public function reschedule(Request $request, Appointment $booking)
    {
        $request->validate([
            'proposed_date' => 'required|date|after_or_equal:today',
            'proposed_time' => 'required|date_format:H:i',
        ]);

        $token = Str::random(48);

        $booking->update([
            'proposed_date'          => $request->proposed_date,
            'proposed_time'          => $request->proposed_time,
            'reschedule_token'       => $token,
            'reschedule_proposed_at' => now(),
            'status'                 => 'reschedule_pending',
        ]);

        $acceptUrl  = route('bookings.accept-reschedule', $token);
        $declineUrl = route('bookings.decline-reschedule', $token);

        try {
            $booking->load(['customer', 'vehicle']);
            Mail::to($booking->customer->email)->send(
                new AppointmentRescheduled($booking, $acceptUrl, $declineUrl)
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to send reschedule email', ['error' => $e->getMessage()]);
        }

        ActivityLog::log('rescheduled', "Reschedule proposal sent for booking {$booking->reference_number}", $booking);

        return back()->with('success', 'Reschedule proposal sent to the customer.');
    }

    public function acceptReschedule(string $token)
    {
        $booking = Appointment::where('reschedule_token', $token)
            ->where('status', 'reschedule_pending')
            ->firstOrFail();

        $newDateTime = $booking->proposed_date . ' ' . $booking->proposed_time . ':00';

        $booking->update([
            'scheduled_date'         => $newDateTime,
            'status'                 => 'confirmed',
            'proposed_date'          => null,
            'proposed_time'          => null,
            'reschedule_token'       => null,
            'reschedule_proposed_at' => null,
        ]);

        $booking->load(['customer', 'vehicle']);

        try {
            Mail::to($booking->customer->email)->send(new AppointmentConfirmation($booking));
        } catch (\Exception $e) {
            \Log::warning('Failed to send confirmation after reschedule accept', ['error' => $e->getMessage()]);
        }

        return Inertia::render('PublicBooking/RescheduleResponse', [
            'status'  => 'accepted',
            'message' => 'Your new appointment time has been confirmed. A confirmation email has been sent to you.',
            'booking' => [
                'reference_number' => $booking->reference_number,
                'scheduled_date'   => $booking->scheduled_date->format('l, d F Y'),
                'scheduled_time'   => $booking->scheduled_date->format('g:i A'),
            ],
        ]);
    }

    public function declineReschedule(string $token)
    {
        $booking = Appointment::where('reschedule_token', $token)
            ->where('status', 'reschedule_pending')
            ->firstOrFail();

        $booking->update([
            'status'                 => 'confirmed',
            'proposed_date'          => null,
            'proposed_time'          => null,
            'reschedule_token'       => null,
            'reschedule_proposed_at' => null,
        ]);

        ActivityLog::log('reschedule_declined', "Customer declined reschedule for booking {$booking->reference_number}", $booking);

        return Inertia::render('PublicBooking/RescheduleResponse', [
            'status'  => 'declined',
            'message' => 'You have declined the proposed new time. Your original booking remains confirmed. We will be in touch shortly.',
            'booking' => [
                'reference_number' => $booking->reference_number,
            ],
        ]);
    }

    /**
     * Generate a quote from a booking request and send it to the client for review.
     * The booking is held in 'pending_quote' status until the client approves or declines.
     */
    public function generateQuote(Request $request, Appointment $booking)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.item_type'  => 'required|in:service,part,labour',
            'items.*.description'=> 'required|string|max:500',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes'              => 'nullable|string|max:2000',
            'validity_days'      => 'nullable|integer|min:1|max:365',
            'discount_percentage'=> 'nullable|numeric|min:0|max:100',
        ]);

        // Only one active quote per booking
        if ($booking->quote && in_array($booking->quote->status, ['sent', 'approved'])) {
            return back()->with('error', 'This booking already has an active quote. Decline or expire it first.');
        }

        $booking->load('customer', 'vehicle');

        $quote = Quote::create([
            'customer_id'         => $booking->customer_id,
            'vehicle_id'          => $booking->vehicle_id,
            'appointment_id'      => $booking->id,
            'quote_date'          => now()->toDateString(),
            'validity_days'       => $request->input('validity_days', 14),
            'status'              => 'draft',
            'description'         => $booking->description ?? $booking->appointment_type,
            'notes'               => $request->input('notes'),
            'discount_percentage' => $request->input('discount_percentage', 0),
        ]);

        foreach ($request->input('items') as $itemData) {
            QuoteItem::create([
                'quote_id'    => $quote->id,
                'item_type'   => $itemData['item_type'],
                'service_id'  => $itemData['service_id'] ?? null,
                'part_id'     => $itemData['part_id'] ?? null,
                'description' => $itemData['description'],
                'quantity'    => $itemData['quantity'],
                'unit_price'  => $itemData['unit_price'],
            ]);
        }

        $quote->calculateTotals();
        $token = $quote->generateReviewToken();

        // Update booking status to pending_quote
        $booking->update(['status' => 'pending_quote']);

        // Send the quote review email to the customer
        $reviewUrl = route('quote.review', $token);
        try {
            Mail::to($booking->customer->email)
                ->send(new QuoteReviewRequest($quote, $reviewUrl));
        } catch (\Exception $e) {
            \Log::warning('Failed to send quote review email', ['error' => $e->getMessage()]);
        }

        ActivityLog::log('quote_generated', "Quote {$quote->quote_number} generated from booking {$booking->reference_number} and sent for review", $booking);

        return back()->with('success', "Quote {$quote->quote_number} sent to {$booking->customer->full_name} for review and approval.");
    }
}

