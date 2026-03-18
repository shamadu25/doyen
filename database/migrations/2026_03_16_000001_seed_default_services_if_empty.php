<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only seed if the table is empty — safe to run on existing installs
        if (DB::table('services')->count() > 0) {
            return;
        }

        $now = now();

        $services = [
            // ── Maintenance & MOT ─────────────────────────────────────────
            ['name' => 'MOT Test Only',                  'code' => 'MOT-ONLY',      'category' => 'Maintenance & MOT',                   'icon' => '🔩', 'description' => 'Class 4 MOT test for cars and light vans.',                        'default_price' => 54.85,  'vat_rate' => 0.00,  'estimated_duration_minutes' => 60,  'sort_order' => 1],
            ['name' => 'Full Service',                   'code' => 'SRV-FULL',      'category' => 'Maintenance & MOT',                   'icon' => '🔩', 'description' => 'Comprehensive full vehicle service with oil, filters and inspection.',    'default_price' => 250.00, 'vat_rate' => 20.00, 'estimated_duration_minutes' => 180, 'sort_order' => 2],
            ['name' => 'Interim Service',                'code' => 'SRV-INT',       'category' => 'Maintenance & MOT',                   'icon' => '🔩', 'description' => 'Interim service including oil and filter change.',                      'default_price' => 120.00, 'vat_rate' => 20.00, 'estimated_duration_minutes' => 90,  'sort_order' => 3],
            ['name' => 'Oil & Oil Filter Change',        'code' => 'SRV-OIL',       'category' => 'Maintenance & MOT',                   'icon' => '🔩', 'description' => 'Engine oil and oil filter replacement.',                                'default_price' => 65.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 45,  'sort_order' => 4],
            ['name' => 'Full Service + MOT',             'code' => 'SRV-FULL-MOT',  'category' => 'Maintenance & MOT',                   'icon' => '🔩', 'description' => 'Full service combined with MOT test — best value.',                     'default_price' => 279.00, 'vat_rate' => 20.00, 'estimated_duration_minutes' => 240, 'sort_order' => 5],
            ['name' => 'Interim Service + MOT',          'code' => 'SRV-INT-MOT',   'category' => 'Maintenance & MOT',                   'icon' => '🔩', 'description' => 'Interim service combined with MOT test.',                               'default_price' => 159.00, 'vat_rate' => 20.00, 'estimated_duration_minutes' => 150, 'sort_order' => 6],
            ['name' => 'Oil/Filter Change + MOT',        'code' => 'SRV-OIL-MOT',   'category' => 'Maintenance & MOT',                   'icon' => '🔩', 'description' => 'Oil and filter change with MOT test.',                                  'default_price' => 99.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 105, 'sort_order' => 7],
            // ── General Vehicle Repairs ───────────────────────────────────
            ['name' => 'Engine Repairs & Servicing',     'code' => 'REP-ENG',       'category' => 'General Vehicle Repairs',             'icon' => '🔧', 'description' => 'Engine diagnostics, repair and servicing.',                            'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 1],
            ['name' => 'Brake Repair & Replacement',     'code' => 'REP-BRK',       'category' => 'General Vehicle Repairs',             'icon' => '🔧', 'description' => 'Front and rear brake pads, discs and calipers.',                       'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 2],
            ['name' => 'Suspension & Steering Repairs',  'code' => 'REP-SUS',       'category' => 'General Vehicle Repairs',             'icon' => '🔧', 'description' => 'Suspension components, steering and wheel alignment.',                  'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 3],
            ['name' => 'Clutch & Gearbox Repairs',       'code' => 'REP-CLT',       'category' => 'General Vehicle Repairs',             'icon' => '🔧', 'description' => 'Clutch replacement and gearbox repair.',                                'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 4],
            ['name' => 'Timing Belt & Chain Replacement','code' => 'REP-TMB',       'category' => 'General Vehicle Repairs',             'icon' => '🔧', 'description' => 'Timing belt and timing chain replacement.',                             'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 5],
            // ── Advanced Diagnostics & ECU Services ──────────────────────
            ['name' => 'ECU Diagnostics & Fault Finding','code' => 'DIAG-ECU',      'category' => 'Advanced Diagnostics & ECU Services', 'icon' => '💻', 'description' => 'Dealer-level ECU diagnostics and fault code analysis.',               'default_price' => 65.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 60,  'sort_order' => 1],
            ['name' => 'ECU Testing & Replacement',      'code' => 'ECU-TEST',      'category' => 'Advanced Diagnostics & ECU Services', 'icon' => '💻', 'description' => 'ECU bench testing, repair and replacement.',                          'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 2],
            ['name' => 'ECU Coding & Programming',       'code' => 'ECU-CODE',      'category' => 'Advanced Diagnostics & ECU Services', 'icon' => '💻', 'description' => 'ECU coding, programming and recalibration.',                          'default_price' => 85.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 90,  'sort_order' => 3],
            ['name' => 'Immobiliser Fault Diagnosis',    'code' => 'ECU-IMMO',      'category' => 'Advanced Diagnostics & ECU Services', 'icon' => '💻', 'description' => 'Immobiliser diagnosis and bypass.',                                   'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 4],
            ['name' => 'Key Cutting & Programming',      'code' => 'ECU-KEY',       'category' => 'Advanced Diagnostics & ECU Services', 'icon' => '💻', 'description' => 'Key cutting, programming and synchronisation.',                       'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 5],
            // ── Airbag & Safety System Services ──────────────────────────
            ['name' => 'Airbag Crash Data Removal',      'code' => 'AIR-CRASH',     'category' => 'Airbag & Safety System Services',     'icon' => '🛡️', 'description' => 'Clear airbag crash data after accident.',                              'default_price' => 75.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 60,  'sort_order' => 1],
            ['name' => 'SRS Module Reset',               'code' => 'AIR-SRS',       'category' => 'Airbag & Safety System Services',     'icon' => '🛡️', 'description' => 'SRS airbag module reset and repair.',                                  'default_price' => 95.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 90,  'sort_order' => 2],
            ['name' => 'Airbag Module Repair',           'code' => 'AIR-MOD',       'category' => 'Airbag & Safety System Services',     'icon' => '🛡️', 'description' => 'Airbag module bench repair — save on costly replacements.',           'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 3],
            ['name' => 'Seatbelt Pretensioner Reset',    'code' => 'AIR-BELT',      'category' => 'Airbag & Safety System Services',     'icon' => '🛡️', 'description' => 'Seatbelt pretensioner reset and repair.',                              'default_price' => 65.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 60,  'sort_order' => 4],
            ['name' => 'Airbag Light Diagnostics',       'code' => 'AIR-DIAG',      'category' => 'Airbag & Safety System Services',     'icon' => '🛡️', 'description' => 'Diagnose and clear airbag warning lights.',                           'default_price' => 55.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 45,  'sort_order' => 5],
            // ── ECU Remapping & Performance Tuning ────────────────────────
            ['name' => 'Stage 1 Performance Remap',      'code' => 'REMAP-S1',      'category' => 'ECU Remapping & Performance Tuning',  'icon' => '⚡', 'description' => 'Stage 1 ECU remap — increased power and torque.',                    'default_price' => 295.00, 'vat_rate' => 20.00, 'estimated_duration_minutes' => 120, 'sort_order' => 1],
            ['name' => 'Stage 2 Performance Remap',      'code' => 'REMAP-S2',      'category' => 'ECU Remapping & Performance Tuning',  'icon' => '⚡', 'description' => 'Stage 2 ECU remap — requires hardware modifications.',               'default_price' => 450.00, 'vat_rate' => 20.00, 'estimated_duration_minutes' => 180, 'sort_order' => 2],
            ['name' => 'Eco & Fuel Economy Remap',       'code' => 'REMAP-ECO',     'category' => 'ECU Remapping & Performance Tuning',  'icon' => '⚡', 'description' => 'Economy remap to improve MPG and reduce fuel consumption.',           'default_price' => 250.00, 'vat_rate' => 20.00, 'estimated_duration_minutes' => 120, 'sort_order' => 3],
            ['name' => 'Gearbox (TCU) Tuning',           'code' => 'REMAP-TCU',     'category' => 'ECU Remapping & Performance Tuning',  'icon' => '⚡', 'description' => 'Transmission control unit tuning for better gear changes.',           'default_price' => 295.00, 'vat_rate' => 20.00, 'estimated_duration_minutes' => 120, 'sort_order' => 4],
            ['name' => 'Custom Tuning Solution',         'code' => 'REMAP-CUST',    'category' => 'ECU Remapping & Performance Tuning',  'icon' => '⚡', 'description' => 'Bespoke tuning — contact us to discuss your requirements.',          'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 5],
            // ── Emission Services ─────────────────────────────────────────
            ['name' => 'DPF Repair / Off',               'code' => 'EMIS-DPF',      'category' => 'Emission Services',                   'icon' => '♻️', 'description' => 'DPF diagnostics, repair or removal.',                                  'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 1],
            ['name' => 'EGR Repair / Off',               'code' => 'EMIS-EGR',      'category' => 'Emission Services',                   'icon' => '♻️', 'description' => 'EGR diagnostics, repair or removal.',                                  'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 2],
            ['name' => 'AdBlue / SCR Repair or Off',     'code' => 'EMIS-ADBLUE',   'category' => 'Emission Services',                   'icon' => '♻️', 'description' => 'AdBlue and SCR system repair or emulation.',                           'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 3],
            ['name' => 'Oxygen (Lambda) Sensor Repair',  'code' => 'EMIS-O2',       'category' => 'Emission Services',                   'icon' => '♻️', 'description' => 'Oxygen/lambda sensor diagnosis and replacement.',                      'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 4],
            ['name' => 'DTC Delete',                     'code' => 'EMIS-DTC',      'category' => 'Emission Services',                   'icon' => '♻️', 'description' => 'Diagnostic trouble code deletion and emissions reset.',                 'default_price' => 55.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 45,  'sort_order' => 5],
            // ── Mileage Correction Services ──────────────────────────────
            ['name' => 'Mileage Correction',             'code' => 'MILE-CORR',     'category' => 'Mileage Correction Services',          'icon' => '🖥️', 'description' => 'Instrument cluster mileage correction — legal applications only.',    'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 1],
            ['name' => 'Instrument Cluster Replacement', 'code' => 'MILE-CLUST',    'category' => 'Mileage Correction Services',          'icon' => '🖥️', 'description' => 'Instrument cluster replacement and coding.',                          'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 2],
            ['name' => 'Dashboard Display Repairs',      'code' => 'MILE-DASH',     'category' => 'Mileage Correction Services',          'icon' => '🖥️', 'description' => 'Dashboard pixel repair and display replacement.',                      'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 3],
            // ── Electrical & Electronic Repairs ──────────────────────────
            ['name' => 'Wiring Fault Tracing',           'code' => 'ELEC-WIRE',     'category' => 'Electrical & Electronic Repairs',     'icon' => '🔌', 'description' => 'Wiring loom fault finding and repair.',                                'default_price' => 65.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 1],
            ['name' => 'CAN Network Diagnostics',        'code' => 'ELEC-CAN',      'category' => 'Electrical & Electronic Repairs',     'icon' => '🔌', 'description' => 'CAN bus and network communication fault diagnosis.',                    'default_price' => 85.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 90,  'sort_order' => 2],
            ['name' => 'Battery Drain Diagnosis',        'code' => 'ELEC-BAT',      'category' => 'Electrical & Electronic Repairs',     'icon' => '🔌', 'description' => 'Identify parasitic battery drain faults.',                              'default_price' => 55.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 60,  'sort_order' => 3],
            ['name' => 'Starter & Alternator Testing',   'code' => 'ELEC-SALT',     'category' => 'Electrical & Electronic Repairs',     'icon' => '🔌', 'description' => 'Starter motor and alternator health check and replacement.',           'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 4],
            // ── Commercial & Fleet Services ───────────────────────────────
            ['name' => 'Van Diagnostics',                'code' => 'FLEET-DIAG',    'category' => 'Commercial & Fleet Services',         'icon' => '🚚', 'description' => 'Commercial vehicle diagnostics and fault finding.',                     'default_price' => 75.00,  'vat_rate' => 20.00, 'estimated_duration_minutes' => 60,  'sort_order' => 1],
            ['name' => 'Sprinter / Iveco / Transit Specialist Work', 'code' => 'FLEET-SPEC', 'category' => 'Commercial & Fleet Services', 'icon' => '🚚', 'description' => 'Specialist work on Sprinter, Iveco Daily and Ford Transit vans.',     'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 2],
            ['name' => 'Fleet Maintenance Support',      'code' => 'FLEET-MAINT',   'category' => 'Commercial & Fleet Services',         'icon' => '🚚', 'description' => 'Scheduled fleet maintenance and servicing programmes.',                 'default_price' => 0.00,   'vat_rate' => 20.00, 'estimated_duration_minutes' => 0,   'sort_order' => 3],
        ];

        foreach ($services as $item) {
            DB::table('services')->insert(array_merge($item, [
                'is_active'           => 1,
                'is_approved'         => 1,
                'show_on_website'     => 1,
                'requires_booking'    => 1,
                'cost_price'          => 0.00,
                'website_description' => $item['description'],
                'created_at'          => $now,
                'updated_at'          => $now,
            ]));
        }
    }

    public function down(): void
    {
        // Not reversible — don't drop manually-managed service data
    }
};
