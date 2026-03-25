<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'assigned_to',
        'scheduled_date',
        'scheduled_end_date',
        'duration_minutes',
        'appointment_type',
        'status',
        'cancellation_reason',
        'description',
        'customer_notes',
        'internal_notes',
        'reminder_sent',
        'reminder_sent_at',
        'reference_number',
        'customer_photos',
        'proposed_date',
        'proposed_time',
        'reschedule_token',
        'reschedule_proposed_at',
        'reschedule_requested_date',
        'reschedule_requested_time',
        'reschedule_notes',
    ];

    protected $appends = ['reference', 'scheduled_time'];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'scheduled_end_date' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'reschedule_proposed_at' => 'datetime',
        'reschedule_requested_date' => 'date',
        'customer_photos' => 'array',
    ];

    public function getScheduledTimeAttribute(): ?string
    {
        return $this->scheduled_date?->format('H:i');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (empty($appointment->reference_number)) {
                $year   = date('Y');
                $prefix = 'DA-' . $year . '-';
                // Use max() over existing refs (including soft-deleted) so failed
                // or rolled-back transactions never cause a duplicate reference.
                $lastRef    = static::withTrashed()
                    ->where('reference_number', 'like', $prefix . '%')
                    ->orderBy('reference_number', 'desc')
                    ->value('reference_number');
                $nextNumber = $lastRef ? (intval(substr($lastRef, strlen($prefix))) + 1) : 1;
                $appointment->reference_number = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function jobCard()
    {
        return $this->hasOne(JobCard::class);
    }

    public function quote()
    {
        return $this->hasOne(Quote::class);
    }

    public function getReferenceAttribute(): ?string
    {
        return $this->reference_number;
    }
}
