<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['registration'];

    protected $fillable = [
        'customer_id',
        'registration_number',
        'vin',
        'make',
        'model',
        'variant',
        'color',
        'year',
        'fuel_type',
        'transmission',
        'engine_size',
        'mileage',
        'mot_due_date',
        'tax_due_date',
        'service_due_date',
        'service_due_mileage',
        'dvla_data',
        'tecdoc_data',
        'notes',
        'is_active',
        'photos',
        'main_photo',
    ];

    protected $casts = [
        'dvla_data' => 'array',
        'tecdoc_data' => 'array',
        'photos' => 'array',
        'is_active' => 'boolean',
        'mot_due_date' => 'date',
        'tax_due_date' => 'date',
        'service_due_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function jobCards()
    {
        return $this->hasMany(JobCard::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function motTests()
    {
        return $this->hasMany(MotTest::class);
    }

    public function services()
    {
        return $this->hasMany(VehicleService::class);
    }

    public function serviceReminders()
    {
        return $this->hasMany(ServiceReminder::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function healthChecks()
    {
        return $this->hasMany(VehicleHealthCheck::class);
    }

    /**
     * Check if service is due
     */
    public function isServiceDue($days = 30)
    {
        if ($this->service_due_date && $this->service_due_date <= now()->addDays($days)) {
            return true;
        }

        // Check service reminders
        foreach ($this->serviceReminders as $reminder) {
            if ($reminder->checkIfDue()) {
                return true;
            }
        }

        return false;
    }

    public function getDisplayNameAttribute()
    {
        return "{$this->registration_number} - {$this->make} {$this->model}";
    }

    public function getRegistrationAttribute(): ?string
    {
        return $this->registration_number;
    }
}
