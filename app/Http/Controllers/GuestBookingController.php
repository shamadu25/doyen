<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class GuestBookingController extends Controller
{
    public function create()
    {
        return Inertia::render('GuestBooking', [
            'availableSlots' => $this->getAvailableSlots(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Customer details
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postcode' => ['required', 'string', 'max:20'],
            
            // Vehicle details
            'registration_number' => ['required', 'string', 'max:20'],
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'colour' => ['nullable', 'string', 'max:50'],
            'fuel_type' => ['nullable', 'string', Rule::in(['petrol', 'diesel', 'electric', 'hybrid', 'lpg', 'other'])],
            'transmission' => ['nullable', 'string', Rule::in(['manual', 'automatic', 'semi-automatic'])],
            'mileage' => ['nullable', 'integer', 'min:0'],
            
            // Appointment details
            'appointment_type' => ['required', 'string', Rule::in(['mot', 'service', 'repair', 'diagnosis'])],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'description' => ['required', 'string', 'max:1000'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            
            // Optional account creation
            'create_account' => ['boolean'],
            'password' => ['required_if:create_account,true', 'nullable', 'string', 'min:8', 'confirmed'],
        ]);

        DB::beginTransaction();

        try {
            // Check if customer already exists by email
            $customer = Customer::where('email', $validated['email'])->first();
            
            if (!$customer) {
                // Create new customer
                $customer = Customer::create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'postcode' => $validated['postcode'] ?? null,
                    'customer_type' => 'individual',
                    'status' => 'active',
                ]);
            }

            // Check if vehicle already exists
            $vehicle = Vehicle::where('registration_number', strtoupper($validated['registration_number']))
                ->where('customer_id', $customer->id)
                ->first();

            if (!$vehicle) {
                // Create new vehicle
                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'registration_number' => strtoupper($validated['registration_number']),
                    'make' => $validated['make'],
                    'model' => $validated['model'],
                    'year' => $validated['year'] ?? null,
                    'colour' => $validated['colour'] ?? null,
                    'fuel_type' => $validated['fuel_type'] ?? null,
                    'transmission' => $validated['transmission'] ?? null,
                    'current_mileage' => $validated['mileage'] ?? null,
                    'status' => 'active',
                ]);
            }

            // Create appointment
            $scheduledDateTime = $validated['scheduled_date'] . ' ' . $validated['scheduled_time'] . ':00';
            
            $appointment = Appointment::create([
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'appointment_type' => $validated['appointment_type'],
                'scheduled_date' => $scheduledDateTime,
                'duration_minutes' => $this->getDefaultDuration($validated['appointment_type']),
                'status' => 'pending',
                'description' => $validated['description'],
                'customer_notes' => $validated['customer_notes'] ?? null,
            ]);

            // Create user account if requested
            if ($validated['create_account'] ?? false) {
                if (!$customer->user) {
                    $customer->user()->create([
                        'name' => $customer->full_name,
                        'email' => $customer->email,
                        'password' => Hash::make($validated['password']),
                        'role' => 'customer',
                        'is_active' => true,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('booking.confirmation', $appointment->id)
                ->with('success', 'Your booking has been submitted successfully! We will contact you shortly to confirm.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'An error occurred while creating your booking. Please try again or call us directly.'
            ])->withInput();
        }
    }

    public function confirmation(Appointment $appointment)
    {
        $appointment->load(['customer', 'vehicle']);
        
        return Inertia::render('BookingConfirmation', [
            'booking' => [
                'reference' => $appointment->reference_number,
                'customer_name' => $appointment->customer->full_name,
                'email' => $appointment->customer->email,
                'phone' => $appointment->customer->phone,
                'vehicle' => $appointment->vehicle->registration_number . ' - ' . $appointment->vehicle->make . ' ' . $appointment->vehicle->model,
                'appointment_type' => $appointment->appointment_type,
                'scheduled_date' => $appointment->scheduled_date->format('l, jS F Y'),
                'scheduled_time' => $appointment->scheduled_date->format('g:i A'),
                'description' => $appointment->description,
                'status' => $appointment->status,
            ],
        ]);
    }

    private function getAvailableSlots(): array
    {
        // Load configurable booking hours from settings
        $rawHours = \App\Models\Setting::get('booking_hours');
        $bookingHours = $rawHours ? json_decode($rawHours, true) : [
            'monday'    => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
            'tuesday'   => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
            'wednesday' => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
            'thursday'  => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
            'friday'    => ['open' => true,  'start' => '08:00', 'end' => '17:00'],
            'saturday'  => ['open' => true,  'start' => '09:00', 'end' => '13:00'],
            'sunday'    => ['open' => false, 'start' => '09:00', 'end' => '12:00'],
        ];

        $rawClosed = \App\Models\Setting::get('booking_closed_dates', '[]');
        $closedDates = json_decode($rawClosed, true) ?? [];

        $slots = [];

        for ($i = 1; $i <= 14; $i++) {
            $date    = now()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            $dayName = strtolower($date->format('l'));

            if (in_array($dateStr, $closedDates, true)) continue;

            $dayConfig = $bookingHours[$dayName] ?? ['open' => false];
            if (empty($dayConfig['open'])) continue;

            $start  = \Carbon\Carbon::createFromFormat('H:i', $dayConfig['start'] ?? '09:00');
            $end    = \Carbon\Carbon::createFromFormat('H:i', $dayConfig['end']   ?? '17:00');
            $cursor = $start->copy();
            $times  = [];
            while ($cursor->lt($end)) {
                $times[] = $cursor->format('H:i');
                $cursor->addMinutes(30);
            }

            if (!empty($times)) {
                $slots[] = [
                    'date'      => $dateStr,
                    'day_name'  => $date->format('l'),
                    'available' => true,
                ];
            }
        }

        return $slots;
    }

    private function getDefaultDuration(string $appointmentType): int
    {
        return match($appointmentType) {
            'mot' => 60,
            'service' => 120,
            'repair' => 180,
            'diagnosis' => 90,
            default => 60,
        };
    }
}
