<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_card_id',
        'customer_id',
        'scheduled_for',
        'sent_at',
        'status',
        'review_link',
        'clicked_at',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    /**
     * Get the job card for this review request.
     */
    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }

    /**
     * Get the customer for this review request.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Mark this review request as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark this review request as clicked.
     */
    public function markAsClicked(): void
    {
        $this->update([
            'status' => 'clicked',
            'clicked_at' => now(),
        ]);
    }

    /**
     * Mark this review as completed.
     */
    public function markAsReviewed(): void
    {
        $this->update([
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Cancel this review request.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    /**
     * Check if this review request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if this review request was sent.
     */
    public function wasSent(): bool
    {
        return in_array($this->status, ['sent', 'clicked']);
    }
}
