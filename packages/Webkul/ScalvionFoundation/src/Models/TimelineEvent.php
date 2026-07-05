<?php

namespace Webkul\ScalvionFoundation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Webkul\User\Models\UserProxy;

class TimelineEvent extends Model
{
    protected $table = 'scalvion_timeline_events';

    protected $fillable = [
        'subject_type',
        'subject_id',
        'user_id',
        'event_type',
        'title',
        'description',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }
}
