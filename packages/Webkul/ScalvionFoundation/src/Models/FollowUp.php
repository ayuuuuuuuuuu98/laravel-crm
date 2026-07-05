<?php

namespace Webkul\ScalvionFoundation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Webkul\User\Models\UserProxy;

class FollowUp extends Model
{
    protected $table = 'scalvion_follow_ups';

    protected $fillable = [
        'followupable_type',
        'followupable_id',
        'assigned_user_id',
        'created_by',
        'completed_by',
        'title',
        'note',
        'status',
        'remind_at',
        'snoozed_until',
        'completed_at',
        'meta',
    ];

    protected $casts = [
        'remind_at'     => 'datetime',
        'snoozed_until' => 'datetime',
        'completed_at'  => 'datetime',
        'meta'          => 'array',
    ];

    public function followupable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'completed_by');
    }
}
