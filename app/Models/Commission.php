<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_card_id',
        'invoice_id',
        'type',
        'base_amount',
        'commission_rate',
        'commission_amount',
        'status',
        'period_start',
        'period_end',
        'paid_date',
        'description',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function approve()
    {
        $this->update(['status' => 'approved']);
    }

    public function markAsPaid($date = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => $date ?? now(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('period_end', [$start, $end]);
    }

    public static function calculateForJobCard(JobCard $jobCard, User $technician)
    {
        $invoice = $jobCard->invoice;
        if (!$invoice) {
            return null;
        }

        $baseAmount = $invoice->total;
        $commissionRate = $technician->commission_rate ?? 5;
        $commissionAmount = ($baseAmount * $commissionRate) / 100;

        return self::create([
            'user_id' => $technician->id,
            'job_card_id' => $jobCard->id,
            'invoice_id' => $invoice->id,
            'type' => 'service',
            'base_amount' => $baseAmount,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'status' => 'pending',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'description' => "Commission for Job Card #{$jobCard->id}",
        ]);
    }
}
