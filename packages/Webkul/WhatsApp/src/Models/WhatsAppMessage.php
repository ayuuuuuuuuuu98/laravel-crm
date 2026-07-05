<?php

namespace Webkul\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'conversation_id',
        'account_id',
        'direction',
        'message_type',
        'content',
        'external_message_id',
        'status',
        'sender_name',
        'recipient_phone',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class);
    }

    public function getDirectionLabelAttribute(): string
    {
        return $this->direction === 'outbound' ? 'Sent' : 'Received';
    }
}
