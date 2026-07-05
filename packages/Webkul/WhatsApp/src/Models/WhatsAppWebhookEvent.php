<?php

namespace Webkul\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppWebhookEvent extends Model
{
    protected $table = 'whatsapp_webhook_events';

    protected $fillable = [
        'account_id',
        'event_type',
        'payload',
        'processed_at',
        'success',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
        'success' => 'boolean',
    ];
}
