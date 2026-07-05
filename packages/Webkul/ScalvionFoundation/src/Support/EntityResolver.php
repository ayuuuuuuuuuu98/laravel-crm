<?php

namespace Webkul\ScalvionFoundation\Support;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;
use Webkul\Lead\Models\Lead;
use Webkul\WhatsApp\Models\WhatsAppConversation;

class EntityResolver
{
    public function resolveType(string $type): string
    {
        return match (strtolower($type)) {
            'contact', 'person', 'persons', Person::class => Person::class,
            'company', 'organization', 'organizations', Organization::class => Organization::class,
            'lead', 'leads', 'deal', 'deals', Lead::class => Lead::class,
            'whatsapp', 'whatsapp_conversation', 'whatsapp_conversations', WhatsAppConversation::class => WhatsAppConversation::class,
            default => throw new InvalidArgumentException("Unsupported entity type [{$type}]"),
        };
    }

    public function find(string $type, int $id): Model
    {
        $modelClass = $this->resolveType($type);

        return $modelClass::query()->findOrFail($id);
    }

    public function label(Model $model): string
    {
        return match ($model::class) {
            Person::class               => $model->name,
            Organization::class         => $model->name,
            Lead::class                 => $model->title,
            WhatsAppConversation::class => $model->contact_label ?? $model->contact_identifier ?? "Conversation #{$model->id}",
            default                     => class_basename($model).' #'.$model->getKey(),
        };
    }

    public function shortType(Model|string $model): string
    {
        $class = $model instanceof Model ? $model::class : $this->resolveType($model);

        return match ($class) {
            Person::class               => 'person',
            Organization::class         => 'organization',
            Lead::class                 => 'lead',
            WhatsAppConversation::class => 'whatsapp',
            default                     => 'entity',
        };
    }
}
