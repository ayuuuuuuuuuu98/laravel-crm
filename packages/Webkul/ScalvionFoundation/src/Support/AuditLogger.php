<?php

namespace Webkul\ScalvionFoundation\Support;

use Illuminate\Database\Eloquent\Model;
use Webkul\ScalvionFoundation\Models\AuditLog;
use Webkul\ScalvionFoundation\Models\TimelineEvent;

class AuditLogger
{
    public function log(Model $model, string $action, array $before = [], array $after = [], array $meta = []): AuditLog
    {
        return AuditLog::query()->create([
            'auditable_type' => $model::class,
            'auditable_id'   => $model->getKey(),
            'user_id'        => auth()->id(),
            'action'         => $action,
            'before'         => $before ?: null,
            'after'          => $after ?: null,
            'meta'           => $meta ?: null,
        ]);
    }

    public function addTimelineEvent(Model $model, string $eventType, string $title, ?string $description = null, array $payload = []): TimelineEvent
    {
        return TimelineEvent::query()->create([
            'subject_type' => $model::class,
            'subject_id'   => $model->getKey(),
            'user_id'      => auth()->id(),
            'event_type'   => $eventType,
            'title'        => $title,
            'description'  => $description,
            'payload'      => $payload ?: null,
        ]);
    }
}
