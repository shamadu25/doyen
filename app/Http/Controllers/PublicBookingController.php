<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Mail\AdminBookingAlert;
use App\Mail\AppointmentConfirmation;
use App\Mail\BookingSubmitted;
use App\Services\SmsService;
use App\Services\VehicleDataGlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class PublicBookingController extends Controller
{
    private const DEFAULT_BOOKING_HOURS = [
        'monday'    => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
        'tuesday'   => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
        'wednesday' => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
        'thursday'  => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
        'friday'    => ['open' => true,  'start' => '08:00', 'end' => '17:00'],
        'saturday'  => ['open' => true,  'start' => '09:00', 'end' => '13:00'],
        'sunday'    => ['open' => false, 'start' => '09:00', 'end' => '12:00'],
    ];

    public function create()
    {
        $settings    = \App\Models\Setting::getAllSettings();
        $bookingHours = isset($settings['booking_hours'])
            ? json_decode($settings['booking_hours'], true)
            : self::DEFAULT_BOOKING_HOURS;
        $closedDates = isset($settings['booking_closed_dates'])
            ? array_values(json_decode($settings['booking_closed_dates'], true))
            : [];
        $slotDuration = (int)($settings['booking_slot_duration'] ?? 30);

        return Inertia::render('PublicBooking/Create', [
            'bookingHours' => $bookingHours,
            'closedDates'  => $closedDates,
            'slotDuration' => $slotDuration,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_type' => 'nullable|in:existing,new',

            // Customer details
            'customer_first_name' => 'required|string|max:255',
            'customer_last_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'customer_postcode' => 'required|string|max:10|regex:/^[A-Za-z0-9 ]{2,10}$/',
            
            // Vehicle details
            'vehicle_registration' => 'required|string|max:20',
            'vehicle_make' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_colour' => 'nullable|string|max:50',
            'vehicle_mileage' => 'nullable|integer|min:0',
            'vehicle_fuel_type' => 'nullable|string|max:50',
            'vehicle_engine_size' => 'nullable|integer|min:0',
            'vehicle_mot_due_date' => 'nullable|date',
            'vehicle_tax_due_date' => 'nullable|date',
            
            // Booking details
            'requested_service' => 'required|in:'
                // Maintenance & MOT
                . 'full-service,interim-service,oil-filter-change,'
                . 'full-service-mot,interim-service-mot,oil-filter-change-mot,mot-only,'
                // General repairs
                . 'general-repairs-maintenance,full-vehicle-diagnostics,engine-repairs-servicing,'
                . 'brake-repair-replacement,suspension-steering-repairs,clutch-gearbox-repairs,'
                . 'timing-belt-chain-replacement,mot-preparation,'
                // ECU & diagnostics
                . 'ecu-testing-fault-code-analysis,ecu-repair-cloning,module-coding-programming,'
                . 'immobiliser-programming,key-cutting-programming,'
                // Airbag & safety
                . 'airbag-crash-data-reset,airbag-module-repair,seatbelt-pretensioner-reset,airbag-light-diagnostics,'
                // Emissions
                . 'dpf-repair-off,egr-repair-off,adblue-scr-repair,lambda-oxygen-repair,dtc-delete,'
                . 'dpf-egr-adblue-solutions,adblue-system-diagnostics,nox-sensor-replacement,egr-system-diagnostics,'
                // Performance
                . 'ecu-remapping,stage-1-tuning,stage-2-tuning,eco-fuel-remap,gearbox-tcu-tuning,custom-tuning,software-updates,'
                // Mileage correction
                . 'instrument-cluster-replacement,mileage-correction,dashboard-display-repair,'
                // Electrical
                . 'electrical-fault-tracing,battery-drain-diagnosis,starter-alternator-testing,'
                // Commercial
                . 'commercial-fleet,fleet-maintenance',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|date_format:H:i',
            'quote_request' => 'nullable|string|max:1000',
            'description' => 'nullable|string|max:1000',
            'customer_notes' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx|max:10240',

            // Optional portal account creation
            'create_account' => 'nullable|boolean',
            'password' => 'required_if:create_account,1|nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Find or create customer
            $customer = Customer::where('email', $request->customer_email)->first();
            
            if (!$customer) {
                $customer = Customer::create([
                    'first_name' => $request->customer_first_name,
                    'last_name' => $request->customer_last_name,
                    'email' => $request->customer_email,
                    'phone' => $request->customer_phone,
                    'address' => $request->customer_address ?? '',
                    'city' => 'Glasgow', // Default city
                    'postcode' => $request->customer_postcode ?? '',
                    'customer_type' => 'individual',
                    'is_active' => true,
                ]);
            } else {
                // Update customer details if they've changed
                $customer->update([
                    'first_name' => $request->customer_first_name,
                    'last_name' => $request->customer_last_name,
                    'phone' => $request->customer_phone,
                    'address' => $request->customer_address ?? $customer->address,
                    'postcode' => $request->customer_postcode ?? $customer->postcode,
                ]);
            }

            // Find or create vehicle — lookup by registration ONLY (UK plate is globally unique)
            $reg = strtoupper(str_replace(' ', '', $request->vehicle_registration));
            $vehicle = Vehicle::updateOrCreate(
                ['registration_number' => $reg],
                [
                    'customer_id'  => $customer->id,
                    'make'         => $request->vehicle_make,
                    'model'        => $request->vehicle_model,
                    'year'         => $request->vehicle_year ?? date('Y'),
                    'color'        => $request->vehicle_colour,
                    'mileage'      => $request->vehicle_mileage ?? 0,
                    'fuel_type'    => $request->vehicle_fuel_type,
                    'engine_size'  => $request->vehicle_engine_size,
                    'transmission' => $request->vehicle_transmission ?: null,
                    'mot_due_date' => $request->vehicle_mot_due_date ?: null,
                    'tax_due_date' => $request->vehicle_tax_due_date ?: null,
                    'is_active'    => true,
                ]
            );

            // Create appointment
            $scheduledDateTime = $request->scheduled_date . ' ' . $request->scheduled_time . ':00';
            $selectedServiceLabel = $this->getServiceLabel($request->requested_service);
            $appointmentType = $this->getServiceCategory($request->requested_service);

            $descriptionParts = [
                'Requested Service: ' . $selectedServiceLabel,
            ];

            if ($request->filled('quote_request')) {
                $descriptionParts[] = 'Quote Request: ' . $request->quote_request;
            }

            if ($request->filled('description')) {
                $descriptionParts[] = $request->description;
            }

            $combinedDescription = implode("\n\n", $descriptionParts);
            
            $appointment = Appointment::create([
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'appointment_type' => $appointmentType,
                'scheduled_date' => $scheduledDateTime,
                'duration_minutes' => $this->getDefaultDuration($appointmentType),
                'status' => 'pending',
                'description' => $combinedDescription,
                'customer_notes' => $request->customer_notes,
            ]);

            // Create customer portal account if requested
            $accountCreated = false;
            if ($request->boolean('create_account') && $request->filled('password')) {
                if (!$customer->password) {
                    $customer->update(['password' => Hash::make($request->password)]);
                }
                session(['customer_id' => $customer->id]);
                $accountCreated = true;
            }

            DB::commit();

            // Store any attachments
            if ($request->hasFile('attachments')) {
                $attachmentPaths = [];
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('booking-attachments/' . $appointment->id, 'public');
                    $attachmentPaths[] = $path;
                }
                $appointment->update(['customer_photos' => $attachmentPaths]);
            }

            // Notify customer that booking request has been received (pending admin approval)
            try {
                $appointment->load(['customer', 'vehicle']);
                Mail::to($customer->email)->send(new BookingSubmitted($appointment));
            } catch (\Exception $e) {
                \Log::warning('Failed to send booking-submitted email', [
                    'appointment_id' => $appointment->id,
                    'customer_email' => $customer->email,
                    'error' => $e->getMessage()
                ]);
            }

            // Notify admin of new booking via email and SMS
            try {
                $appointment->loadMissing(['customer', 'vehicle']);
                $adminEmail = env('ADMIN_EMAIL', env('GARAGE_EMAIL'));
                if ($adminEmail) {
                    Mail::to($adminEmail)->send(new AdminBookingAlert($appointment, 'new'));
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send admin booking alert email', ['error' => $e->getMessage()]);
            }
            try {
                (new SmsService())->sendAdminBookingAlert($appointment);
            } catch (\Exception $e) {
                \Log::warning('Failed to send admin booking alert SMS', ['error' => $e->getMessage()]);
            }

            return redirect()->route('booking.confirmation', $appointment->id)
                ->with('account_created', $accountCreated);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Public booking creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return back()->withErrors(['error' => 'Failed to create booking. Please try again or call us directly.'])->withInput();
        }
    }

    public function confirmation(Appointment $appointment)
    {
        $appointment->load(['customer', 'vehicle']);
        
        return Inertia::render('PublicBooking/Confirmation', [
            'booking' => [
                'reference' => $appointment->reference_number,
                'customer_name' => $appointment->customer->full_name,
                'email' => $appointment->customer->email,
                'phone' => $appointment->customer->phone,
                'vehicle' => $appointment->vehicle->registration_number . ' - ' . $appointment->vehicle->make . ' ' . $appointment->vehicle->model,
                'appointment_type' => $appointment->appointment_type,
                'service_requested' => $this->extractRequestedService($appointment->description),
                'quote_request' => $this->extractQuoteRequest($appointment->description),
                'scheduled_date' => $appointment->scheduled_date->format('l, jS F Y'),
                'scheduled_time' => $appointment->scheduled_date->format('g:i A'),
                'description' => $this->extractCustomerDescription($appointment->description),
                'status' => $appointment->status,
            ],
            'reference' => $appointment->reference_number,
            'account_created' => (bool) session('account_created'),
            'portal_url' => route('customer.dashboard'),
        ]);
    }

    public function lookupVehicle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid registration number format.',
            ], 422);
        }

        try {
            $vdgService  = app(VehicleDataGlobalService::class);
            $vehicleData = $vdgService->getVehicleDetails(strtoupper($request->registration));

            if ($vehicleData) {
                $formatted = $vdgService->formatVehicleData($vehicleData);
                return response()->json([
                    'success' => true,
                    'data' => [
                        'make'                   => $formatted['make']              ?? '',
                        'model'                  => $formatted['model']             ?? '',
                        'variant'                => $formatted['variant']           ?? null,
                        'year'                   => $formatted['year']              ?? null,
                        'color'                  => $formatted['color']             ?? '',
                        'fuel_type'              => $formatted['fuel_type']         ?? '',
                        'engine_size'            => $formatted['engine_size']       ?? null,
                        'transmission'           => $formatted['transmission']      ?? null,
                        'vin'                    => $formatted['vin']               ?? null,
                        'number_of_doors'        => $formatted['number_of_doors']   ?? null,
                        'number_of_seats'        => $formatted['number_of_seats']   ?? null,
                        'vehicle_type'           => $formatted['vehicle_type']      ?? null,
                        'mot_status'             => $formatted['mot_status']        ?? null,
                        'mot_due_date'           => $formatted['mot_due_date']      ?? null,
                        'tax_status'             => $formatted['tax_status']        ?? null,
                        'tax_due_date'           => $formatted['tax_due_date']      ?? null,
                        'co2_emissions'          => $formatted['co2_emissions']     ?? null,
                        'bhp'                    => $formatted['bhp']               ?? null,
                        'month_first_registered' => $formatted['month_first_registered'] ?? null,
                        'wheelplan'              => $formatted['wheelplan']         ?? null,
                    ],
                    'message' => 'Vehicle found in database',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle not found. Please enter details manually.',
                ], 404);
            }
        } catch (\Exception $e) {
            \Log::error('Vehicle lookup failed', [
                'registration' => $request->registration,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to lookup vehicle. Please enter details manually.',
            ], 500);
        }
    }

    private function getDefaultDuration(string $type): int
    {
        return match($type) {
            'mot' => 60,
            'service' => 120,
            'repair' => 90,
            'diagnosis' => 60,
            default => 60,
        };
    }

    private function getServiceLabel(string $service): string
    {
        return match($service) {
            // Maintenance & MOT
            'full-service'                    => 'Full Service',
            'interim-service'                 => 'Interim Service',
            'oil-filter-change'               => 'Oil & Oil Filter Change',
            'full-service-mot'                => 'Full Service & MOT',
            'interim-service-mot'             => 'Interim Service & MOT',
            'oil-filter-change-mot'           => 'Oil & Filter Change with MOT',
            'mot-only'                        => 'MOT Test',
            // General repairs
            'general-repairs-maintenance'    => 'General Repairs & Maintenance',
            'full-vehicle-diagnostics'       => 'Full Vehicle Diagnostics',
            'engine-repairs-servicing'       => 'Engine Repairs & Servicing',
            'brake-repair-replacement'       => 'Brake Repair & Replacement',
            'suspension-steering-repairs'    => 'Suspension & Steering Repairs',
            'clutch-gearbox-repairs'         => 'Clutch & Gearbox Repairs',
            'timing-belt-chain-replacement'  => 'Timing Belt & Chain Replacement',
            'mot-preparation'                => 'MOT Preparation',
            // ECU & diagnostics
            'ecu-testing-fault-code-analysis' => 'ECU Diagnostics & Fault Finding',
            'ecu-repair-cloning'             => 'ECU Testing & Replacement',
            'module-coding-programming'      => 'ECU Coding & Programming',
            'immobiliser-programming'        => 'Immobiliser Fault Diagnosis',
            'key-cutting-programming'        => 'Key Cutting & Programming',
            // Airbag & safety
            'airbag-crash-data-reset'        => 'Airbag Crash Data Removal (SRS Reset)',
            'airbag-module-repair'           => 'Airbag Module Repair',
            'seatbelt-pretensioner-reset'    => 'Seatbelt Pretensioner Reset',
            'airbag-light-diagnostics'       => 'Airbag Light Diagnostics',
            // Emissions
            'dpf-repair-off'                 => 'DPF Repair / DPF Off',
            'egr-repair-off'                 => 'EGR Repair / EGR Off',
            'adblue-scr-repair'              => 'AdBlue / SCR Repair or Off',
            'lambda-oxygen-repair'           => 'Oxygen (Lambda) Sensor Repair',
            'dtc-delete'                     => 'DTCs Delete',
            'dpf-egr-adblue-solutions'       => 'DPF, EGR & AdBlue Solutions',
            'adblue-system-diagnostics'      => 'AdBlue System Diagnostics',
            'nox-sensor-replacement'         => 'NOx Sensor Replacement',
            'egr-system-diagnostics'         => 'EGR System Diagnostics',
            // Performance
            'ecu-remapping'                  => 'ECU Remapping',
            'stage-1-tuning'                 => 'Stage 1 Performance Remap',
            'stage-2-tuning'                 => 'Stage 2 Performance Remap',
            'eco-fuel-remap'                 => 'Eco & Fuel Economy Remap',
            'gearbox-tcu-tuning'             => 'Gearbox (TCU) Tuning',
            'custom-tuning'                  => 'Custom Tuning Solution',
            'software-updates'               => 'Software Updates',
            // Mileage correction
            'instrument-cluster-replacement' => 'Instrument Cluster Replacement',
            'mileage-correction'             => 'Mileage Correction',
            'dashboard-display-repair'       => 'Dashboard Display Repair',
            // Electrical
            'electrical-fault-tracing'       => 'Electrical Fault Tracing & CAN Diagnostics',
            'battery-drain-diagnosis'        => 'Battery Drain Diagnosis',
            'starter-alternator-testing'     => 'Starter & Alternator Testing',
            // Commercial
            'commercial-fleet'               => 'Commercial Van Diagnostics',
            'fleet-maintenance'              => 'Fleet Maintenance Support',
            default => ucfirst(str_replace('-', ' ', $service)),
        };
    }

    private function getServiceCategory(string $service): string
    {
        return match(true) {
            in_array($service, [
                'engine-repairs-servicing', 'brake-repair-replacement', 'suspension-steering-repairs',
                'clutch-gearbox-repairs', 'timing-belt-chain-replacement',
                'ecu-repair-cloning',
                'airbag-crash-data-reset', 'airbag-module-repair', 'seatbelt-pretensioner-reset',
                'dpf-egr-adblue-solutions', 'nox-sensor-replacement',
                'dpf-repair-off', 'egr-repair-off', 'adblue-scr-repair', 'lambda-oxygen-repair',
                'instrument-cluster-replacement', 'dashboard-display-repair',
            ]) => 'repair',
            in_array($service, [
                'mot-preparation',
                'full-service-mot', 'interim-service-mot', 'oil-filter-change-mot', 'mot-only',
            ]) => 'mot',
            in_array($service, [
                'general-repairs-maintenance', 'key-cutting-programming',
                'ecu-remapping', 'stage-1-tuning', 'software-updates',
                'commercial-fleet', 'fleet-maintenance',
                'full-service', 'interim-service', 'oil-filter-change',
                'stage-2-tuning', 'eco-fuel-remap', 'gearbox-tcu-tuning', 'custom-tuning',
                'dtc-delete', 'mileage-correction',
            ]) => 'service',
            default => 'diagnosis',
        };
    }

    private function extractRequestedService(?string $description): ?string
    {
        if (!$description) {
            return null;
        }

        if (preg_match('/^Requested Service:\s*(.+)$/m', $description, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function extractQuoteRequest(?string $description): ?string
    {
        if (!$description) {
            return null;
        }

        if (preg_match('/^Quote Request:\s*(.+)$/m', $description, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function extractCustomerDescription(?string $description): ?string
    {
        if (!$description) {
            return null;
        }

        $cleaned = preg_replace('/^Requested Service:\s*.+$/m', '', $description);
        $cleaned = preg_replace('/^Quote Request:\s*.+$/m', '', $cleaned ?? '');
        $cleaned = trim(preg_replace('/\n{3,}/', "\n\n", $cleaned ?? ''));

        return $cleaned !== '' ? $cleaned : null;
    }
}
