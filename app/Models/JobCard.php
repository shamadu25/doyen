<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_number',
        'customer_id',
        'vehicle_id',
        'appointment_id',
        'assigned_to',
        'mileage_in',
        'mileage_out',
        'date_in',
        'date_out',
        'promised_date',
        'status',
        'completion_date',
        'priority',
        'customer_complaint',
        'work_required',
        'work_completed',
        'technician_notes',
        'parts_required',
        'estimated_cost',
        'actual_cost',
        'customer_waiting',
        'courtesy_car',
        'vehicle_location',
    ];

    protected $casts = [
        'date_in' => 'datetime',
        'date_out' => 'datetime',
        'promised_date' => 'datetime',
        'completion_date' => 'datetime',
        'customer_waiting' => 'boolean',
        'courtesy_car' => 'boolean',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($jobCard) {
            if (!$jobCard->job_number) {
                $jobCard->job_number = static::generateJobNumber();
            }
        });
    }

    public static function generateJobNumber()
    {
        $year = date('Y');
        $lastJob = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastJob ? ((int) substr($lastJob->job_number, -5)) + 1 : 1;
        
        return 'JOB-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function services()
    {
        return $this->hasMany(JobCardService::class);
    }

    public function parts()
    {
        return $this->hasMany(JobCardPart::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function healthChecks()
    {
        return $this->hasMany(VehicleHealthCheck::class);
    }

    public function reviewRequest()
    {
        return $this->hasOne(ReviewRequest::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function sourceQuote()
    {
        return $this->hasOne(Quote::class, 'converted_to_job_card_id');
    }

    public function getTotalCostAttribute()
    {
        $servicesTotal = $this->services->sum(function ($service) {
            return ($service->unit_price * $service->quantity) - $service->discount;
        });

        $partsTotal = $this->parts->sum(function ($part) {
            return ($part->unit_price * $part->quantity) - $part->discount;
        });

        return $servicesTotal + $partsTotal;
    }

    public function calculateTotals(): void
    {
        $this->load(['services', 'parts']);

        $servicesTotal = $this->services->sum(function ($service) {
            $price = $service->price ?? $service->unit_price ?? 0;
            return $price * ($service->quantity ?? 1);
        });

        $partsTotal = $this->parts->sum(function ($part) {
            $price = $part->selling_price ?? $part->unit_price ?? 0;
            return $price * ($part->quantity ?? 1);
        });

        $this->update(['estimated_cost' => $servicesTotal + $partsTotal]);
    }
}
