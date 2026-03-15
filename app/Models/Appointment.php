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
                $appointment->reference_number = 'DA-' . date('Y') . '-' . str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 5, '0', STR_PAD_LEFT);
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
