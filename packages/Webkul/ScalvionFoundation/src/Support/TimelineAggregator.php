<?php

namespace Webkul\ScalvionFoundation\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Webkul\ScalvionFoundation\Models\AuditLog;
use Webkul\ScalvionFoundation\Models\FollowUp;
use Webkul\ScalvionFoundation\Models\TimelineEvent;

class TimelineAggregator
{
    public function forEntity(Model $entity, int $limit = 50): Collection
    {
        $items = collect();

        if (method_exists($entity, 'activities')) {
            $items = $items->merge(
                $entity->activities()
                    ->with('user')
                    ->latest()
                    ->limit($limit)
                    ->get()
                    ->map(fn ($activity) => [
                        'source'      => 'activity',
                        'type'        => $activity->type,
                        'title'       => $activity->title,
                        'description' => $activity->comment,
                        'user'        => optional($activity->user)->name,
                        'created_at'  => optional($activity->created_at)?->toIso8601String(),
                    ])
            );
        }

        $items = $items->merge(
            TimelineEvent::query()
                ->where('subject_type', $entity::class)
                ->where('subject_id', $entity->getKey())
                ->with('user')
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn ($event) => [
                    'source'      => 'timeline',
                    'type'        => $event->event_type,
                    'title'       => $event->title,
                    'description' => $event->description,
                    'user'        => optional($event->user)->name,
                    'created_at'  => optional($event->created_at)?->toIso8601String(),
                    'payload'     => $event->payload,
                ])
        );

        $items = $items->merge(
            FollowUp::query()
                ->where('followupable_type', $entity::class)
                ->where('followupable_id', $entity->getKey())
                ->with(['assignedUser', 'creator'])
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn ($followUp) => [
                    'source'      => 'follow_up',
                    'type'        => $followUp->status,
                    'title'       => $followUp->title,
                    'description' => $followUp->note,
                    'user'        => optional($followUp->assignedUser)->name ?: optional($followUp->creator)->name,
                    'created_at'  => optional($followUp->updated_at)?->toIso8601String(),
                    'payload'     => [
                        'remind_at' => optional($followUp->remind_at)?->toIso8601String(),
                        'status'    => $followUp->status,
                    ],
                ])
        );

        $items = $items->merge(
            AuditLog::query()
                ->where('auditable_type', $entity::class)
                ->where('auditable_id', $entity->getKey())
                ->whereIn('action', ['update', 'status_change', 'assignment_change'])
                ->with('user')
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn ($audit) => [
                    'source'      => 'audit',
                    'type'        => $audit->action,
                    'title'       => ucfirst(str_replace('_', ' ', $audit->action)),
                    'description' => $this->formatAuditDescription($audit),
                    'user'        => optional($audit->user)->name,
                    'created_at'  => optional($audit->created_at)?->toIso8601String(),
                    'payload'     => [
                        'before' => $audit->before,
                        'after'  => $audit->after,
                    ],
                ])
        );

        $entityClass = $entity::class;
        if ($entityClass === \Webkul\WhatsApp\Models\WhatsAppConversation::class && method_exists($entity, 'messages')) {
            $items = $items->merge(
                $entity->messages()
                    ->latest()
                    ->limit(20)
                    ->get()
                    ->map(fn ($msg) => [
                        'source'      => 'whatsapp',
                        'type'        => $msg->direction === 'inbound' ? 'inbound' : 'outbound',
                        'title'       => $msg->direction === 'inbound' ? 'Message received' : 'Message sent',
                        'description' => $msg->content,
                        'user'        => null,
                        'created_at'  => optional($msg->created_at)?->toIso8601String(),
                        'payload'     => [
                            'direction' => $msg->direction,
                            'type'      => $msg->message_type,
                        ],
                    ])
            );
        }

        $items = $items->sortByDesc('created_at')->values();

        return $items->take($limit);
    }

    public function auditsForEntity(Model $entity, int $limit = 50): Collection
    {
        return AuditLog::query()
            ->where('auditable_type', $entity::class)
            ->where('auditable_id', $entity->getKey())
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($audit) => [
                'action'     => $audit->action,
                'before'     => $audit->before,
                'after'      => $audit->after,
                'meta'       => $audit->meta,
                'user'       => optional($audit->user)->name,
                'created_at' => optional($audit->created_at)?->toIso8601String(),
            ]);
    }

    protected function formatAuditDescription($audit): ?string
    {
        $before = $audit->before;
        $after = $audit->after;

        if (empty($before) || empty($after)) {
            return null;
        }

        $changed = [];
        foreach ($after as $key => $value) {
            if (isset($before[$key]) && $before[$key] !== $value) {
                $changed[] = str_replace('_', ' ', $key).' changed';
            }
        }

        return $changed ? implode(', ', $changed) : null;
    }
}
