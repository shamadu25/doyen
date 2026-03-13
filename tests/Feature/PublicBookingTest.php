<?php

namespace Tests\Feature;

use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmation;
use App\Mail\BookingSubmitted;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Services\VehicleDataGlobalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicBookingTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    /** Minimal valid payload to POST /book-online */
    private function bookingPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_type'        => 'new',
            'customer_first_name'  => 'John',
            'customer_last_name'   => 'Smith',
            'customer_email'       => 'john.smith@example.com',
            'customer_phone'       => '07700900000',
            'customer_address'     => '123 High Street',
            'customer_postcode'    => 'G1 1AA',

            'vehicle_registration' => 'AB12CDE',
            'vehicle_make'         => 'VAUXHALL',
            'vehicle_model'        => 'ASTRA',
            'vehicle_year'         => 2017,
            'vehicle_colour'       => 'SILVER',
            'vehicle_fuel_type'    => 'PETROL',
            'vehicle_engine_size'  => 1400,
            'vehicle_transmission' => 'Manual',
            'vehicle_mot_due_date' => '2026-06-11',
            'vehicle_tax_due_date' => '',

            'requested_service'    => 'mot-only',
            'scheduled_date'       => now()->addDays(7)->format('Y-m-d'),
            'scheduled_time'       => '09:00',
            'description'          => 'Please check tyres too.',
            'customer_notes'       => 'Early morning preferred.',
        ], $overrides);
    }

    /** Fake VDG service returning realistic data for AB12CDE */
    private function mockVdgService(): void
    {
        $this->instance(VehicleDataGlobalService::class, new class {
            public function getVehicleDetails(string $reg): ?array
            {
                if (strtoupper($reg) === 'AB12CDE') {
                    return [
                        'VehicleRegistration' => [
                            'Make'             => 'VAUXHALL',
                            'Model'            => 'ASTRA SRI TURBO',
                            'TransmissionType' => 'Manual',
                            'FuelType'         => 'Petrol',
                            'Colour'           => 'Silver',
                            'EngineCapacity'   => 1400,
                            'YearOfManufacture'=> 2017,
                            'Vrm'              => 'AB12CDE',
                        ],
                        'VehicleStatus' => [
                            'NextMotDueDate'        => '11/06/2026',
                            'DaysUntilNextMotIsDue' => 97,
                        ],
                        'TechnicalDetails' => [
                            'Performance' => [
                                'Power' => ['Bhp' => 148],
                                'Co2'   => 128,
                            ],
                            'Dimensions' => [
                                'NumberOfDoors' => 5,
                                'NumberOfSeats' => 5,
                            ],
                        ],
                        'SmmtDetails' => ['BodyStyle' => 'Hatchback', 'ModelVariant' => 'SRI TURBO'],
                        'MotHistory'  => ['RecordList' => []],
                    ];
                }
                return null;
            }

            public function formatVehicleData(array $raw): array
            {
                return [
                    'make'                   => 'VAUXHALL',
                    'model'                  => 'ASTRA SRI TURBO',
                    'variant'                => 'SRI TURBO',
                    'year'                   => 2017,
                    'color'                  => 'Silver',
                    'fuel_type'              => 'Petrol',
                    'engine_size'            => 1400,
                    'transmission'           => 'Manual',
                    'vin'                    => null,
                    'number_of_doors'        => 5,
                    'number_of_seats'        => 5,
                    'vehicle_type'           => null,
                    'mot_status'             => 'VALID',
                    'mot_due_date'           => '2026-06-11',
                    'tax_status'             => null,
                    'tax_due_date'           => null,
                    'co2_emissions'          => 128,
                    'bhp'                    => 148,
                    'month_first_registered' => null,
                    'wheelplan'              => null,
                ];
            }
        });
    }

    // ---------------------------------------------------------------------------
    // 1. Booking form page loads
    // ---------------------------------------------------------------------------

    public function test_booking_form_loads(): void
    {
        $response = $this->get('/book-online');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('PublicBooking/Create'));
    }

    // ---------------------------------------------------------------------------
    // 2. Vehicle lookup — known registration
    // ---------------------------------------------------------------------------

    public function test_vehicle_lookup_returns_data_for_known_reg(): void
    {
        $this->mockVdgService();

        $response = $this->postJson('/api/vehicle-lookup', ['registration' => 'AB12CDE']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'make'         => 'VAUXHALL',
                    'model'        => 'ASTRA SRI TURBO',
                    'transmission' => 'Manual',
                    'year'         => 2017,
                    'fuel_type'    => 'Petrol',
                    'mot_status'   => 'VALID',
                    'mot_due_date' => '2026-06-11',
                    'bhp'          => 148,
                    'co2_emissions'=> 128,
                ],
            ]);
    }

    // ---------------------------------------------------------------------------
    // 3. Vehicle lookup — unknown registration returns 404
    // ---------------------------------------------------------------------------

    public function test_vehicle_lookup_returns_404_for_unknown_reg(): void
    {
        $this->instance(VehicleDataGlobalService::class, new class {
            public function getVehicleDetails(string $reg): ?array { return null; }
            public function formatVehicleData(array $raw): array { return []; }
        });

        $response = $this->postJson('/api/vehicle-lookup', ['registration' => 'UNKNOWN1']);

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    // ---------------------------------------------------------------------------
    // 4. Vehicle lookup — invalid registration is rejected (validation)
    // ---------------------------------------------------------------------------

    public function test_vehicle_lookup_rejects_empty_registration(): void
    {
        $response = $this->postJson('/api/vehicle-lookup', ['registration' => '']);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    // ---------------------------------------------------------------------------
    // 5. Full booking submission — happy path
    // ---------------------------------------------------------------------------

    public function test_booking_creates_customer_vehicle_and_appointment(): void
    {
        Mail::fake();

        $response = $this->post('/book-online', $this->bookingPayload());

        // Should redirect to confirmation
        $response->assertRedirectContains('/booking-confirmation/');

        // Customer created
        $this->assertDatabaseHas('customers', [
            'email'      => 'john.smith@example.com',
            'first_name' => 'John',
            'last_name'  => 'Smith',
        ]);

        // Vehicle created with all VDG fields
        $this->assertDatabaseHas('vehicles', [
            'registration_number' => 'AB12CDE',
            'make'                => 'VAUXHALL',
            'model'               => 'ASTRA',
            'transmission'        => 'Manual',
            'fuel_type'           => 'PETROL',
            'year'                => 2017,
        ]);

        // Appointment created
        $customer     = Customer::where('email', 'john.smith@example.com')->first();
        $vehicle      = Vehicle::where('registration_number', 'AB12CDE')->first();
        $appointment  = Appointment::where('vehicle_id', $vehicle->id)->first();

        $this->assertNotNull($appointment);
        $this->assertEquals($customer->id, $appointment->customer_id);
        $this->assertEquals('pending', $appointment->status);
        $this->assertStringContainsString('MOT Test', $appointment->description);
        $this->assertNotEmpty($appointment->reference_number);
    }

    // ---------------------------------------------------------------------------
    // 6. Booking confirmation page renders
    // ---------------------------------------------------------------------------

    public function test_booking_confirmation_page_renders(): void
    {
        Mail::fake();

        $this->post('/book-online', $this->bookingPayload());

        $appointment = Appointment::latest()->first();

        $response = $this->get("/booking-confirmation/{$appointment->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('PublicBooking/Confirmation')
            ->has('booking')
            ->where('booking.status', 'pending')
        );
    }

    // ---------------------------------------------------------------------------
    // 7. Duplicate booking — same reg, existing customer gets updated appointment
    // ---------------------------------------------------------------------------

    public function test_second_booking_for_same_registration_reuses_vehicle(): void
    {
        Mail::fake();

        $payload = $this->bookingPayload();

        // First booking
        $this->post('/book-online', $payload);
        $this->assertDatabaseCount('vehicles', 1);

        // Second booking — same reg, different service
        $payload['requested_service'] = 'full-service';
        $payload['scheduled_date']    = now()->addDays(14)->format('Y-m-d');
        $this->post('/book-online', $payload);

        // Still only one vehicle record (updateOrCreate by registration)
        $this->assertDatabaseCount('vehicles', 1);
        // But two appointments
        $this->assertDatabaseCount('appointments', 2);
    }

    // ---------------------------------------------------------------------------
    // 8. Validation — required fields enforced
    // ---------------------------------------------------------------------------

    public function test_booking_requires_customer_first_name(): void
    {
        $response = $this->post('/book-online', $this->bookingPayload(['customer_first_name' => '']));
        $response->assertSessionHasErrors('customer_first_name');
    }

    public function test_booking_requires_valid_email(): void
    {
        $response = $this->post('/book-online', $this->bookingPayload(['customer_email' => 'not-an-email']));
        $response->assertSessionHasErrors('customer_email');
    }

    public function test_booking_requires_vehicle_registration(): void
    {
        $response = $this->post('/book-online', $this->bookingPayload(['vehicle_registration' => '']));
        $response->assertSessionHasErrors('vehicle_registration');
    }

    public function test_booking_requires_vehicle_make(): void
    {
        $response = $this->post('/book-online', $this->bookingPayload(['vehicle_make' => '']));
        $response->assertSessionHasErrors('vehicle_make');
    }

    public function test_booking_requires_vehicle_model(): void
    {
        $response = $this->post('/book-online', $this->bookingPayload(['vehicle_model' => '']));
        $response->assertSessionHasErrors('vehicle_model');
    }

    public function test_booking_requires_valid_service(): void
    {
        $response = $this->post('/book-online', $this->bookingPayload(['requested_service' => 'invalid-service']));
        $response->assertSessionHasErrors('requested_service');
    }

    public function test_booking_date_must_not_be_in_past(): void
    {
        $response = $this->post('/book-online', $this->bookingPayload([
            'scheduled_date' => now()->subDays(1)->format('Y-m-d'),
        ]));
        $response->assertSessionHasErrors('scheduled_date');
    }

    public function test_booking_requires_valid_time_format(): void
    {
        $response = $this->post('/book-online', $this->bookingPayload(['scheduled_time' => 'not-a-time']));
        $response->assertSessionHasErrors('scheduled_time');
    }

    // ---------------------------------------------------------------------------
    // 9. On booking submit — BookingSubmitted email is sent (not confirmation)
    // ---------------------------------------------------------------------------

    public function test_booking_submitted_email_is_sent_on_booking(): void
    {
        Mail::fake();

        $this->post('/book-online', $this->bookingPayload());

        // Should send the "received / pending" email
        Mail::assertSent(BookingSubmitted::class, function ($mail) {
            return $mail->hasTo('john.smith@example.com');
        });

        // Should NOT send the confirmed email yet
        Mail::assertNotSent(AppointmentConfirmation::class);
    }

    // ---------------------------------------------------------------------------
    // 10. Admin confirms booking — sends AppointmentConfirmation email
    // ---------------------------------------------------------------------------

    public function test_admin_confirm_sends_confirmation_email(): void
    {
        Mail::fake();

        // Create customer + vehicle + appointment directly
        $customer = Customer::factory()->create(['email' => 'jane.doe@example.com']);
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $booking  = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id'  => $vehicle->id,
            'status'      => 'pending',
        ]);

        $admin = \App\Models\User::factory()->create();
        $this->actingAs($admin);

        $this->post("/bookings/{$booking->id}/confirm");

        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);

        Mail::assertSent(AppointmentConfirmation::class, fn ($m) => $m->hasTo('jane.doe@example.com'));
        Mail::assertNotSent(BookingSubmitted::class);
    }

    // ---------------------------------------------------------------------------
    // 11. Admin cancels booking — sends AppointmentCancelled email
    // ---------------------------------------------------------------------------

    public function test_admin_cancel_sends_cancellation_email(): void
    {
        Mail::fake();

        $customer = Customer::factory()->create(['email' => 'cancel.me@example.com']);
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $booking  = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id'  => $vehicle->id,
            'status'      => 'confirmed',
        ]);

        $admin = \App\Models\User::factory()->create();
        $this->actingAs($admin);

        $this->post("/bookings/{$booking->id}/cancel");

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);

        Mail::assertSent(AppointmentCancelled::class, fn ($m) => $m->hasTo('cancel.me@example.com'));
    }

    // ---------------------------------------------------------------------------
    // 12. Reference number is generated
    // ---------------------------------------------------------------------------

    public function test_appointment_gets_reference_number(): void
    {
        Mail::fake();

        $this->post('/book-online', $this->bookingPayload());

        $appointment = Appointment::latest()->first();

        $this->assertNotNull($appointment->reference_number);
        $this->assertStringStartsWith('DA-', $appointment->reference_number);
    }
}
