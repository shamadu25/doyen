<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppConversation extends Model
{
    protected $fillable = [
        'customer_phone', 'customer_name', 'customer_id',
        'status', 'unread_count', 'last_message', 'last_message_at',
        'assigned_to', 'notes',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function messages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeHasUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function markAllRead(): void
    {
        $this->messages()->whereNull('read_at')->where('direction', 'inbound')->update(['read_at' => now()]);
        $this->update(['unread_count' => 0]);
    }

    public function addInboundMessage(string $body, ?string $sid = null, ?string $mediaUrl = null, ?string $mediaType = null): WhatsAppMessage
    {
        $msg = $this->messages()->create([
            'direction'          => 'inbound',
            'body'               => $body,
            'twilio_sid'         => $sid,
            'status'             => 'received',
            'media_url'          => $mediaUrl,
            'media_content_type' => $mediaType,
        ]);

        $this->increment('unread_count');
        $this->update([
            'last_message'    => $body,
            'last_message_at' => now(),
            'status'          => $this->status === 'closed' ? 'open' : $this->status,
        ]);

        return $msg;
    }

    public function addOutboundMessage(string $body, ?int $sentBy = null, ?string $sid = null): WhatsAppMessage
    {
        $msg = $this->messages()->create([
            'direction' => 'outbound',
            'body'      => $body,
            'twilio_sid' => $sid,
            'status'    => 'queued',
            'sent_by'   => $sentBy,
        ]);

        $this->update([
            'last_message'    => $body,
            'last_message_at' => now(),
        ]);

        return $msg;
    }
}
