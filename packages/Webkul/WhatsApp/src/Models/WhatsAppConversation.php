<?php

namespace Webkul\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkul\Activity\Models\ActivityProxy;
use Webkul\Contact\Models\PersonProxy;
use Webkul\Lead\Models\LeadProxy;
use Webkul\Tag\Models\TagProxy;
use Webkul\User\Models\UserProxy;

class WhatsAppConversation extends Model
{
    use SoftDeletes;

    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'account_id',
        'contact_identifier',
        'person_id',
        'lead_id',
        'assigned_user_id',
        'owner_user_id',
        'last_message_at',
        'unread_count',
        'status',
        'priority',
        'next_action',
        'follow_up_at',
        'pinned_at',
        'archived_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_message_at' => 'datetime',
        'follow_up_at' => 'datetime',
        'pinned_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(PersonProxy::modelClass());
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'assigned_user_id');
    }

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'owner_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            TagProxy::modelClass(),
            'whatsapp_conversation_tags',
            'whatsapp_conversation_id',
            'tag_id'
        );
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(
            ActivityProxy::modelClass(),
            'whatsapp_conversation_activities',
            'whatsapp_conversation_id',
            'activity_id'
        );
    }

    public function getContactLabelAttribute(): string
    {
        return $this->person?->name
            ?? $this->lead?->title
            ?? $this->contact_identifier
            ?? '';
    }

    public function getLastMessagePreviewAttribute(): string
    {
        $message = $this->relationLoaded('messages')
            ? $this->messages->sortByDesc('created_at')->first()
            : $this->messages()->latest('created_at')->latest('id')->first();

        if (! $message?->content) {
            return 'No messages yet';
        }

        return str($message->content)->limit(120)->toString();
    }

    public function getIsPinnedAttribute(): bool
    {
        return ! is_null($this->pinned_at);
    }

    public function getIsArchivedAttribute(): bool
    {
        return ! is_null($this->archived_at);
    }

    public function getIsUnreadAttribute(): bool
    {
        return (int) $this->unread_count > 0;
    }
}
