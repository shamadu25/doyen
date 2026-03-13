<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    protected $fillable = [
        'conversation_id', 'direction', 'body', 'twilio_sid',
        'status', 'media_url', 'media_content_type', 'sent_by', 'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function conversation()
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }

    public function hasMedia(): bool
    {
        return !empty($this->media_url);
    }
}
