<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ServiceReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'reminder_type',
        'service_type',
        'interval_months',
        'interval_miles',
        'last_service_mileage',
        'last_service_date',
        'current_mileage',
        'next_due_date',
        'next_due_mileage',
        'is_due',
        'reminder_sent',
        'last_reminder_sent',
    ];

    protected $casts = [
        'last_service_date' => 'date',
        'next_due_date' => 'date',
        'is_due' => 'boolean',
        'reminder_sent' => 'boolean',
        'last_reminder_sent' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Check if service is due
     */
    public function checkIfDue()
    {
        $isDue = false;

        if ($this->reminder_type === 'time_based' || $this->reminder_type === 'both') {
            if ($this->next_due_date && $this->next_due_date <= Carbon::today()->addDays(30)) {
                $isDue = true;
            }
        }

        if ($this->reminder_type === 'mileage_based' || $this->reminder_type === 'both') {
            if ($this->next_due_mileage && $this->current_mileage >= ($this->next_due_mileage - 500)) {
                $isDue = true;
            }
        }

        if ($isDue !== $this->is_due) {
            $this->update(['is_due' => $isDue]);
        }

        return $isDue;
    }

    /**
     * Calculate next due date/mileage
     */
    public function calculateNextDue()
    {
        if ($this->reminder_type === 'time_based' || $this->reminder_type === 'both') {
            if ($this->last_service_date && $this->interval_months) {
                $this->next_due_date = $this->last_service_date->addMonths($this->interval_months);
            }
        }

        if ($this->reminder_type === 'mileage_based' || $this->reminder_type === 'both') {
            if ($this->last_service_mileage && $this->interval_miles) {
                $this->next_due_mileage = $this->last_service_mileage + $this->interval_miles;
            }
        }

        $this->save();
        $this->checkIfDue();
    }

    /**
     * Mark reminder as sent
     */
    public function markReminderSent()
    {
        $this->update([
            'reminder_sent' => true,
            'last_reminder_sent' => now(),
        ]);
    }
}
