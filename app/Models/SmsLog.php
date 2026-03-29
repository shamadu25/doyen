<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'template_key',
        'audience',
        'recipient',
        'normalized_recipient',
        'message',
        'status',
        'provider',
        'provider_reference',
        'error_message',
        'related_type',
        'related_id',
        'metadata',
        'sent_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];
}
