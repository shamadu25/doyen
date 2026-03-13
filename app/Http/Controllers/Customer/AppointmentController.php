<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Vehicle;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentConfirmation;

class AppointmentController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        $appointments = $customer->appointments()
            ->with(['vehicle', 'service'])
            ->latest('appointment_date')
            ->paginate(20);
        
        return view('customer.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $customer = Auth::guard('customer')->user();
        $vehicles = $customer->vehicles;
        $services = Service::where('is_active', true)->get();
        
        // Get available time slots for the next 30 days
        $availableSlots = $this->getAvailableSlots();
        
        return view('customer.appointments.create', compact('vehicles', 'services', 'availableSlots'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required',
            'notes' => 'nullable|string|max:500',
            'photos.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $customer = Auth::guard('customer')->user();
        
        // Check if vehicle belongs to customer
        if (!$customer->vehicles()->where('id', $validated['vehicle_id'])->exists()) {
            return back()->withErrors(['vehicle_id' => 'Invalid vehicle selected.']);
        }

        // Combine date and time
        $appointmentDateTime = $validated['appointment_date'] . ' ' . $validated['appointment_time'];

        // Handle photo uploads
        $photosPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('appointment-photos', 'public');
                $photosPaths[] = $path;
            }
        }

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $validated['vehicle_id'],
            'service_id' => $validated['service_id'],
            'appointment_date' => $appointmentDateTime,
            'status' => 'pending',
            'notes' => $validated['notes'],
            'customer_photos' => !empty($photosPaths) ? $photosPaths : null,
        ]);

        // Send confirmation email
        try {
            Mail::to($customer->email)->send(new AppointmentConfirmation($appointment));
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment confirmation email: ' . $e->getMessage());
        }

        // Send SMS notification if enabled
        if ($customer->phone && $customer->sms_notifications) {
            $this->smsService->sendAppointmentConfirmation($appointment);
        }

        return redirect()->route('customer.appointments.index')
            ->with('success', 'Appointment booked successfully! You will receive a confirmation shortly.');
    }

    public function show(Appointment $appointment)
    {
        $customer = Auth::guard('customer')->user();
        
        if ($appointment->customer_id !== $customer->id) {
            abort(403);
        }

        $appointment->load(['vehicle', 'service', 'jobCard']);

        return view('customer.appointments.show', compact('appointment'));
    }

    public function cancel(Appointment $appointment)
    {
        $customer = Auth::guard('customer')->user();
        
        if ($appointment->customer_id !== $customer->id) {
            abort(403);
        }

        if ($appointment->status === 'completed') {
            return back()->withErrors(['error' => 'Cannot cancel a completed appointment.']);
        }

        $appointment->update(['status' => 'cancelled']);

        // Notify admin via email
        \Mail::to(config('mail.from.address'))->send(
            new \App\Mail\AppointmentCancelled($appointment)
        );

        return redirect()->route('customer.appointments.index')
            ->with('success', 'Appointment cancelled successfully.');
    }

    protected function getAvailableSlots()
    {
        $slots = [];
        $workingHours = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00'];
        
        for ($i = 1; $i <= 30; $i++) {
            $date = now()->addDays($i);
            
            // Skip Sundays
            if ($date->dayOfWeek === 0) {
                continue;
            }
            
            $dateStr = $date->format('Y-m-d');
            
            // Get booked appointments for this date
            $bookedSlots = Appointment::whereDate('appointment_date', $dateStr)
                ->where('status', '!=', 'cancelled')
                ->pluck('appointment_date')
                ->map(function($datetime) {
                    return \Carbon\Carbon::parse($datetime)->format('H:i');
                })
                ->toArray();
            
            $availableForDate = array_diff($workingHours, $bookedSlots);
            
            if (!empty($availableForDate)) {
                $slots[$dateStr] = [
                    'date' => $date->format('l, F j, Y'),
                    'times' => array_values($availableForDate)
                ];
            }
        }
        
        return $slots;
    }
}
