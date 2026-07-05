<?php

namespace Webkul\ScalvionFoundation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Webkul\User\Models\UserProxy;

class AuditLog extends Model
{
    protected $table = 'scalvion_audit_logs';

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'user_id',
        'action',
        'before',
        'after',
        'meta',
    ];

    protected $casts = [
        'before' => 'array',
        'after'  => 'array',
        'meta'   => 'array',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }
}
