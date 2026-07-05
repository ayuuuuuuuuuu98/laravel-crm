<?php

namespace Webkul\ScalvionFoundation\Observers;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Models\Lead;
use Webkul\ScalvionFoundation\Notifications\EntityNotification;
use Webkul\ScalvionFoundation\Support\AuditLogger;
use Webkul\ScalvionFoundation\Support\EntityResolver;
use Webkul\User\Models\User;
use Webkul\WhatsApp\Models\WhatsAppConversation;

class EntityObserver
{
    public function __construct(
        protected AuditLogger $auditLogger,
        protected EntityResolver $entityResolver,
    ) {}

    public function created(Model $model): void
    {
        $label = $this->entityResolver->label($model);

        $this->auditLogger->log($model, 'create', [], $this->sanitize($model->getAttributes()));
        $this->auditLogger->addTimelineEvent($model, 'created', 'Created', $label);
    }

    public function updated(Model $model): void
    {
        $changes = collect($model->getChanges())
            ->except(['updated_at', 'created_at'])
            ->toArray();

        if (empty($changes)) {
            return;
        }

        $before = [];
        $after = [];

        foreach (array_keys($changes) as $key) {
            $before[$key] = $model->getOriginal($key);
            $after[$key] = $model->getAttribute($key);
        }

        $this->auditLogger->log($model, 'update', $this->sanitize($before), $this->sanitize($after));

        foreach (array_keys($changes) as $attribute) {
            $this->auditLogger->addTimelineEvent(
                $model,
                'updated',
                'Updated '.str_replace('_', ' ', $attribute),
                class_basename($model).' '.str_replace('_', ' ', $attribute).' changed'
            );
        }

        $this->notifyAssigneeChanges($model, $before, $after);
    }

    public function deleting(Model $model): void
    {
        $this->auditLogger->log($model, 'delete', $this->sanitize($model->getOriginal()), []);
        $this->auditLogger->addTimelineEvent($model, 'deleted', 'Deleted', $this->entityResolver->label($model));
    }

    protected function notifyAssigneeChanges(Model $model, array $before, array $after): void
    {
        $assigneeKey = match ($model::class) {
            Lead::class                 => 'user_id',
            WhatsAppConversation::class => 'assigned_user_id',
            default                     => 'user_id',
        };

        if (! array_key_exists($assigneeKey, $after) || empty($after[$assigneeKey])) {
            return;
        }

        $user = User::query()->find($after[$assigneeKey]);

        if (! $user || $user->id === auth()->id()) {
            return;
        }

        $user->notify(new EntityNotification(
            'Assignment updated',
            $this->entityResolver->label($model).' was assigned to you.',
            $this->entityUrl($model),
            [
                'entity_type' => $this->entityResolver->shortType($model),
                'entity_id'   => $model->getKey(),
                'before'      => $before[$assigneeKey] ?? null,
                'after'       => $after[$assigneeKey] ?? null,
            ],
        ));
    }

    protected function entityUrl(Model $model): ?string
    {
        return match ($model::class) {
            \Webkul\Contact\Models\Person::class       => route('admin.contacts.persons.view', $model->getKey()),
            \Webkul\Contact\Models\Organization::class => route('admin.contacts.organizations.edit', $model->getKey()),
            Lead::class                                => route('admin.leads.view', $model->getKey()),
            WhatsAppConversation::class                => route('admin.whatsapp.inbox.view', $model->getKey()),
            default                                    => null,
        };
    }

    protected function sanitize(array $attributes): array
    {
        return collect($attributes)
            ->except(['password', 'remember_token', 'updated_at'])
            ->map(fn ($value) => is_array($value) ? $value : (is_object($value) ? (string) json_encode($value) : $value))
            ->toArray();
    }
}
