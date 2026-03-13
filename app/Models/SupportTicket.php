<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'customer_id',
        'subject',
        'message',
        'category',
        'priority',
        'status',
        'assigned_to',
        'last_reply_at',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id')->orderBy('created_at');
    }

    public function publicReplies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id')
            ->where('is_internal', false)
            ->orderBy('created_at');
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'red',
            'high'   => 'orange',
            'medium' => 'yellow',
            'low'    => 'gray',
            default  => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open'        => 'blue',
            'in_progress' => 'yellow',
            'resolved'    => 'green',
            'closed'      => 'gray',
            default       => 'gray',
        };
    }

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }
}
