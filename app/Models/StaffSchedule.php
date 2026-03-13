<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StaffSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'status',
        'clock_in_time',
        'clock_out_time',
        'hours_worked',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'hours_worked' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clockIn()
    {
        $this->update([
            'clock_in_time' => now(),
            'status' => 'clocked_in',
        ]);
    }

    public function clockOut()
    {
        $clockIn = $this->clock_in_time;
        $clockOut = now();
        
        $hoursWorked = $clockIn->diffInMinutes($clockOut) / 60;
        
        // Subtract break time if applicable
        if ($this->break_start && $this->break_end) {
            $breakStart = Carbon::parse($this->break_start);
            $breakEnd = Carbon::parse($this->break_end);
            $breakMinutes = $breakStart->diffInMinutes($breakEnd);
            $hoursWorked -= ($breakMinutes / 60);
        }

        $this->update([
            'clock_out_time' => $clockOut,
            'status' => 'clocked_out',
            'hours_worked' => round($hoursWorked, 2),
        ]);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['clocked_in', 'on_break']);
    }
}
