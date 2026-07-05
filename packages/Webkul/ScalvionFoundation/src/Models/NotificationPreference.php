<?php

namespace Webkul\ScalvionFoundation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\User\Models\UserProxy;

class NotificationPreference extends Model
{
    protected $table = 'scalvion_notification_preferences';

    protected $fillable = [
        'user_id',
        'reminder_notifications',
        'activity_notifications',
        'audit_notifications',
        'whatsapp_notifications',
        'channels',
    ];

    protected $casts = [
        'reminder_notifications' => 'boolean',
        'activity_notifications' => 'boolean',
        'audit_notifications' => 'boolean',
        'whatsapp_notifications' => 'boolean',
        'channels'               => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }
}
