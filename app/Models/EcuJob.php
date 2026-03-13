<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcuJob extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_number',
        'vehicle_id',
        'customer_id',
        'job_card_id',
        'technician_id',
        'category',
        'service_type',
        'service_label',
        'status',
        'date_in',
        'date_completed',
        'mileage',
        'ecu_part_number',
        'ecu_software_version',
        'ecu_hardware_version',
        'immo_ref',
        'fault_codes_found',
        'fault_codes_cleared',
        'all_codes_cleared',
        'work_required',
        'work_performed',
        'pre_condition',
        'post_condition',
        'internal_notes',
        'details',
        'price',
        'is_invoiced',
        'warranty_months',
        'customer_notified',
    ];

    protected $casts = [
        'date_in'             => 'date',
        'date_completed'      => 'date',
        'fault_codes_found'   => 'array',
        'fault_codes_cleared' => 'array',
        'details'             => 'array',
        'all_codes_cleared'   => 'boolean',
        'is_invoiced'         => 'boolean',
        'customer_notified'   => 'boolean',
        'price'               => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ecuJob) {
            if (!$ecuJob->job_number) {
                $ecuJob->job_number = static::generateJobNumber();
            }
        });
    }

    public static function generateJobNumber(): string
    {
        $year  = date('Y');
        $month = date('m');
        $last  = static::withTrashed()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->max('id') ?? 0;
        return 'ECU-' . $year . $month . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    // Scopes
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helpers
    public static function categories(): array
    {
        return [
            'diagnostics'       => 'ECU Diagnostics',
            'remapping'         => 'ECU Remapping',
            'airbag_srs'        => 'Airbag / SRS',
            'emissions'         => 'DPF / EGR / Emissions',
            'immobiliser'       => 'Immobiliser / Keys',
            'mileage_correction'=> 'Mileage Correction',
            'electrical'        => 'Electrical Fault',
            'other'             => 'Other',
        ];
    }

    public static function serviceTypes(): array
    {
        return [
            'diagnostics' => [
                'ecu-testing-fault-code-analysis' => 'ECU Diagnostics & Fault Finding',
                'full-vehicle-diagnostics'        => 'Full Vehicle Diagnostics',
                'ecu-repair-cloning'              => 'ECU Repair & Cloning',
                'module-coding-programming'       => 'ECU Coding & Programming',
                'electrical-fault-tracing'        => 'Electrical Fault Tracing',
            ],
            'remapping' => [
                'ecu-remapping'    => 'ECU Remapping',
                'stage-1-tuning'   => 'Stage 1 Performance Remap',
                'stage-2-tuning'   => 'Stage 2 Performance Remap',
                'eco-fuel-remap'   => 'Eco & Fuel Economy Remap',
                'gearbox-tcu-tuning' => 'Gearbox / TCU Tuning',
                'custom-tuning'    => 'Custom Tuning',
                'software-updates' => 'Software Updates',
            ],
            'airbag_srs' => [
                'airbag-crash-data-reset'      => 'Airbag Crash Data Reset',
                'airbag-module-repair'         => 'Airbag Module Repair',
                'seatbelt-pretensioner-reset'  => 'Seatbelt Pretensioner Reset',
                'airbag-light-diagnostics'     => 'Airbag Light Diagnostics',
            ],
            'emissions' => [
                'dpf-repair-off'          => 'DPF Repair / Off',
                'egr-repair-off'          => 'EGR Repair / Off',
                'adblue-scr-repair'       => 'AdBlue / SCR Repair',
                'lambda-oxygen-repair'    => 'Lambda / Oxygen Sensor Repair',
                'dtc-delete'              => 'DTC Delete',
                'nox-sensor-replacement'  => 'NOx Sensor Replacement',
                'egr-system-diagnostics'  => 'EGR System Diagnostics',
                'adblue-system-diagnostics' => 'AdBlue System Diagnostics',
            ],
            'immobiliser' => [
                'immobiliser-programming' => 'Immobiliser Programming',
                'key-cutting-programming' => 'Key Cutting & Programming',
            ],
            'mileage_correction' => [
                'mileage-correction'             => 'Mileage Correction',
                'instrument-cluster-replacement' => 'Instrument Cluster Replacement',
                'dashboard-display-repair'       => 'Dashboard Display Repair',
            ],
            'electrical' => [
                'battery-drain-diagnosis'        => 'Battery Drain Diagnosis',
                'starter-alternator-testing'     => 'Starter / Alternator Testing',
                'electrical-fault-tracing'       => 'Electrical Fault Tracing',
            ],
            'other' => [
                'other' => 'Other',
            ],
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'booked'      => 'Booked',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'on_hold'     => 'On Hold',
            'cancelled'   => 'Cancelled',
        ];
    }

    public function getCategoryLabelAttribute(): string
    {
        return static::categories()[$this->category] ?? $this->category;
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }
}
