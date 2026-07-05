<?php

namespace Webkul\WhatsApp\Repositories;

use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Core\Eloquent\Repository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Tag\Repositories\TagRepository;
use Webkul\User\Repositories\UserRepository;
use Webkul\WhatsApp\Models\WhatsAppConversation;

class WhatsAppConversationRepository extends Repository
{
    public function __construct(
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository,
        protected ActivityRepository $activityRepository,
        protected UserRepository $userRepository,
        protected TagRepository $tagRepository,
        protected WhatsAppMessageRepository $messageRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    public function model()
    {
        return WhatsAppConversation::class;
    }

    public function findForView(int $id): WhatsAppConversation
    {
        return $this->scopeQuery(function ($query) {
            return $this->applyInboxSelects($query);
        })->with([
            'account',
            'person.organization',
            'lead.stage',
            'assignedUser',
            'ownerUser',
            'tags',
        ])->findOrFail($id);
    }

    public function updateConversation(WhatsAppConversation $conversation, array $data): WhatsAppConversation
    {
        $conversation = $this->findForView($conversation->id);

        $personId = ! empty($data['person_id']) ? (int) $data['person_id'] : null;
        $leadId = ! empty($data['lead_id']) ? (int) $data['lead_id'] : null;

        if ($leadId) {
            $lead = $this->leadRepository->findOrFail($leadId);

            if (
                $personId
                && $lead->person_id
                && $lead->person_id !== $personId
            ) {
                throw ValidationException::withMessages([
                    'lead_id' => trans('whatsapp::app.inbox.detail.errors.lead-person-mismatch'),
                ]);
            }

            $personId ??= $lead->person_id;
        }

        if ($personId) {
            $this->personRepository->findOrFail($personId);
        }

        $payload = [
            'person_id'         => $personId,
            'lead_id'           => $leadId,
            'assigned_user_id'  => ! empty($data['assigned_user_id']) ? (int) $data['assigned_user_id'] : null,
            'owner_user_id'     => ! empty($data['owner_user_id']) ? (int) $data['owner_user_id'] : null,
            'status'            => $data['status'] ?? $conversation->status,
            'priority'          => $data['priority'] ?? $conversation->priority,
            'follow_up_at'      => ! empty($data['follow_up_at']) ? Carbon::parse($data['follow_up_at']) : null,
            'next_action'       => $data['next_action'] ?? null,
        ];

        $original = $conversation->only(array_keys($payload));

        $conversation->update($payload);

        $this->logFieldChanges($conversation->fresh(), $original, $payload);

        return $conversation->fresh(['person.organization', 'lead.stage', 'assignedUser', 'ownerUser', 'tags']);
    }

    public function createLeadFromConversation(WhatsAppConversation $conversation)
    {
        if ($conversation->lead_id) {
            return $this->leadRepository->findOrFail($conversation->lead_id);
        }

        $person = $this->createContactFromConversation($conversation);

        $existingLead = $this->leadRepository->scopeQuery(function ($query) use ($person) {
            return $query->where('person_id', $person->id)
                ->orderByDesc('updated_at')
                ->orderByDesc('id');
        })->first();

        if ($existingLead) {
            $conversation->update(['lead_id' => $existingLead->id]);

            return $existingLead;
        }

        $lead = $this->leadRepository->create([
            'entity_type'   => 'leads',
            'title'         => 'WhatsApp - '.$conversation->contact_label,
            'description'   => $conversation->last_message_preview,
            'lead_value'    => 0,
            'status'        => 1,
            'user_id'       => $conversation->assigned_user_id ?: auth()->guard('user')->id(),
            'person'        => ['id' => $person->id],
        ]);

        $conversation->update([
            'person_id' => $person->id,
            'lead_id'   => $lead->id,
        ]);

        $this->logActivity($conversation, trans('whatsapp::app.activities.lead_created'), [
            'lead_id' => $lead->id,
            'lead_title' => $lead->title,
        ]);

        return $lead;
    }

    public function createContactFromConversation(WhatsAppConversation $conversation)
    {
        if ($conversation->person_id) {
            return $this->personRepository->findOrFail($conversation->person_id);
        }

        $person = $this->findExistingPerson($conversation);

        if (! $person) {
            $person = $this->personRepository->create([
                'entity_type'     => 'persons',
                'name'            => $conversation->contact_label,
                'user_id'         => $conversation->owner_user_id ?: $conversation->assigned_user_id ?: auth()->guard('user')->id(),
                'emails'          => [],
                'contact_numbers' => [[
                    'label' => 'mobile',
                    'value' => $conversation->contact_identifier,
                ]],
            ]);

            $this->logActivity($conversation, trans('whatsapp::app.activities.contact_created'), [
                'person_id' => $person->id,
                'person_name' => $person->name,
            ]);
        }

        $conversation->update(['person_id' => $person->id]);

        return $person;
    }

    public function createInternalNote(WhatsAppConversation $conversation, string $comment)
    {
        $activity = $this->activityRepository->create([
            'type'    => 'note',
            'comment' => $comment,
            'is_done' => 1,
            'user_id' => auth()->guard('user')->id(),
        ]);

        if (! $conversation->activities->contains($activity->id)) {
            $conversation->activities()->attach($activity->id);
        }

        return $activity;
    }

    public function sendLocalMessage(WhatsAppConversation $conversation, string $content)
    {
        $message = $this->messageRepository->createLocalOutboundMessage($conversation, $content);

        $this->logActivity($conversation->fresh(), 'Local outbound message saved', [
            'message_id' => $message->id,
            'direction' => $message->direction,
            'status' => $message->status,
        ]);

        return $message;
    }

    public function paginateForInbox(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->applyInboxSelects($this->model->newQuery())
            ->with(['assignedUser:id,name,image', 'ownerUser:id,name,image', 'person:id,name', 'lead:id,title', 'tags:id,name,color']);

        $this->applyInboxFilters($query, $filters);

        return $query
            ->orderByRaw('CASE WHEN pinned_at IS NULL THEN 1 ELSE 0 END ASC')
            ->orderByDesc('pinned_at')
            ->orderByDesc('latest_message_created_at')
            ->orderByDesc('last_message_at')
            ->paginate($perPage, ['*'], 'page', $filters['page'] ?? 1);
    }

    public function getInboxPayload(array $filters = [], int $perPage = 20): array
    {
        $paginator = $this->paginateForInbox($filters, $perPage);

        return [
            'data' => $paginator->getCollection()->map(fn ($conversation) => $this->transformConversationListItem($conversation))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ];
    }

    public function getConversationPayload(int $id, int $messageLimit = 40, ?int $beforeId = null): array
    {
        $conversation = $this->findForView($id);

        $thread = $this->messageRepository->getThreadForConversation($id, $messageLimit, $beforeId);
        $activities = $conversation->activities()
            ->with(['user', 'files', 'participants.user', 'participants.person'])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $notes = $activities
            ->where('type', 'note')
            ->values();

        return [
            'conversation' => $this->transformConversationDetail($conversation),
            'messages' => collect($thread['messages'])->map(fn ($message) => $this->transformMessage($message))->values(),
            'message_meta' => [
                'has_more' => $thread['has_more'],
                'next_before_id' => $thread['next_before_id'],
            ],
            'activities' => $activities->map(fn ($activity) => $this->transformActivity($activity))->values(),
            'notes' => $notes->map(fn ($note) => $this->transformActivity($note))->values(),
            'suggested_persons' => $this->getSuggestedPersons($conversation)->map(fn ($person) => [
                'id' => $person->id,
                'name' => $person->name,
            ])->values(),
            'suggested_leads' => $this->getSuggestedLeads($conversation)->map(fn ($lead) => [
                'id' => $lead->id,
                'title' => $lead->title,
            ])->values(),
        ];
    }

    public function markAsRead(WhatsAppConversation $conversation): WhatsAppConversation
    {
        $conversation->update(['unread_count' => 0]);
        $this->logActivity($conversation, trans('whatsapp::app.activities.marked_read'));

        return $conversation->fresh();
    }

    public function markAsUnread(WhatsAppConversation $conversation): WhatsAppConversation
    {
        $conversation->update(['unread_count' => max(1, (int) $conversation->unread_count)]);
        $this->logActivity($conversation, trans('whatsapp::app.activities.marked_unread'));

        return $conversation->fresh();
    }

    public function archive(WhatsAppConversation $conversation): WhatsAppConversation
    {
        $conversation->update(['archived_at' => now()]);
        $this->logActivity($conversation, trans('whatsapp::app.activities.archived'));

        return $conversation->fresh();
    }

    public function unarchive(WhatsAppConversation $conversation): WhatsAppConversation
    {
        $conversation->update(['archived_at' => null]);
        $this->logActivity($conversation, trans('whatsapp::app.activities.unarchived'));

        return $conversation->fresh();
    }

    public function pin(WhatsAppConversation $conversation): WhatsAppConversation
    {
        $conversation->update(['pinned_at' => now()]);
        $this->logActivity($conversation, trans('whatsapp::app.activities.pinned'));

        return $conversation->fresh();
    }

    public function unpin(WhatsAppConversation $conversation): WhatsAppConversation
    {
        $conversation->update(['pinned_at' => null]);
        $this->logActivity($conversation, trans('whatsapp::app.activities.unpinned'));

        return $conversation->fresh();
    }

    public function close(WhatsAppConversation $conversation): WhatsAppConversation
    {
        return $this->updateConversation($conversation, ['status' => 'closed'] + $conversation->only([
            'person_id', 'lead_id', 'assigned_user_id', 'owner_user_id', 'priority', 'next_action',
        ]) + ['follow_up_at' => $conversation->follow_up_at]);
    }

    public function reopen(WhatsAppConversation $conversation): WhatsAppConversation
    {
        return $this->updateConversation($conversation, ['status' => 'open'] + $conversation->only([
            'person_id', 'lead_id', 'assigned_user_id', 'owner_user_id', 'priority', 'next_action',
        ]) + ['follow_up_at' => $conversation->follow_up_at]);
    }

    public function softDelete(WhatsAppConversation $conversation): void
    {
        $this->logActivity($conversation, trans('whatsapp::app.activities.deleted'));
        $conversation->delete();
    }

    public function restoreConversation(int $id): WhatsAppConversation
    {
        $conversation = $this->model->withTrashed()->findOrFail($id);
        $conversation->restore();
        $this->logActivity($conversation, trans('whatsapp::app.activities.restored'));

        return $conversation->fresh();
    }

    public function getSuggestedPersons(WhatsAppConversation $conversation)
    {
        $phone = $this->normalizedPhone($conversation->contact_identifier);

        return $this->personRepository->scopeQuery(function ($query) use ($conversation, $phone) {
            return $query->where(function ($personQuery) use ($conversation, $phone) {
                $personQuery->where('name', 'like', '%'.$conversation->contact_label.'%');

                if ($phone) {
                    $personQuery->orWhere('contact_numbers', 'like', '%'.$phone.'%');
                }
            })->orderBy('name')->limit(20);
        })->all();
    }

    public function getSuggestedLeads(WhatsAppConversation $conversation)
    {
        $personId = $conversation->person_id;

        return $this->leadRepository->scopeQuery(function ($query) use ($conversation, $personId) {
            $query->orderByDesc('updated_at')->orderByDesc('id')->limit(20);

            if ($personId) {
                return $query->where('person_id', $personId);
            }

            return $query->where('title', 'like', '%'.$conversation->contact_label.'%');
        })->all();
    }

    protected function findExistingPerson(WhatsAppConversation $conversation)
    {
        $phone = $this->normalizedPhone($conversation->contact_identifier);

        if (! $phone) {
            return null;
        }

        return $this->personRepository->scopeQuery(function ($query) use ($phone) {
            return $query->where('contact_numbers', 'like', '%'.$phone.'%')
                ->orderBy('id');
        })->first();
    }

    protected function normalizedPhone(?string $value): ?string
    {
        $normalized = preg_replace('/\D+/', '', (string) $value);

        return Str::of($normalized)->trim()->value() ?: null;
    }

    protected function applyInboxSelects(Builder $query): Builder
    {
        return $query
            ->select('whatsapp_conversations.*')
            ->selectSub($this->messageRepository->getLatestPreviewQuery(), 'last_message_preview')
            ->selectSub($this->messageRepository->getLatestMessageDateQuery(), 'latest_message_created_at');
    }

    protected function applyInboxFilters(Builder $query, array $filters): void
    {
        if (empty($filters['include_deleted'])) {
            $query->whereNull('deleted_at');
        } else {
            $query->withTrashed();
        }

        if (empty($filters['include_archived'])) {
            $query->whereNull('archived_at');
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('contact_identifier', 'like', '%'.$search.'%')
                    ->orWhereHas('person', fn ($personQuery) => $personQuery->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('lead', fn ($leadQuery) => $leadQuery->where('title', 'like', '%'.$search.'%'))
                    ->orWhereExists($this->messageRepository->getLatestMessageSearchQuery($search));
            });
        }

        if (! empty($filters['unread'])) {
            $query->where('unread_count', '>', 0);
        }

        if (! empty($filters['assigned_user_id'])) {
            $query->where('assigned_user_id', (int) $filters['assigned_user_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['tag_id'])) {
            $query->whereHas('tags', fn ($tagQuery) => $tagQuery->where('tags.id', (int) $filters['tag_id']));
        }
    }

    protected function transformConversationListItem(WhatsAppConversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'contact_label' => $conversation->contact_label,
            'contact_identifier' => $conversation->contact_identifier,
            'avatar_initials' => $this->initials($conversation->contact_label ?: $conversation->contact_identifier),
            'status' => $conversation->status,
            'status_label' => trans('whatsapp::app.inbox.statuses.'.$conversation->status),
            'unread_count' => (int) $conversation->unread_count,
            'is_unread' => $conversation->is_unread,
            'is_pinned' => $conversation->is_pinned,
            'is_archived' => $conversation->is_archived,
            'assigned_user' => $this->transformUser($conversation->assignedUser),
            'owner_user' => $this->transformUser($conversation->ownerUser),
            'priority' => $conversation->priority,
            'priority_label' => trans('whatsapp::app.inbox.priorities.'.$conversation->priority),
            'status_tone' => $this->statusTone($conversation->status),
            'priority_tone' => $this->priorityTone($conversation->priority),
            'last_message_preview' => $conversation->last_message_preview ?: trans('whatsapp::app.inbox.datagrid.no_preview'),
            'last_message_at' => optional($conversation->latest_message_created_at ?: $conversation->last_message_at)?->format('M d, Y h:i A'),
            'last_message_time' => optional($conversation->latest_message_created_at ?: $conversation->last_message_at)?->format('h:i A'),
            'tags' => $conversation->tags->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->name, 'color' => $tag->color])->values(),
        ];
    }

    protected function transformConversationDetail(WhatsAppConversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'contact_label' => $conversation->contact_label,
            'contact_identifier' => $conversation->contact_identifier,
            'avatar_initials' => $this->initials($conversation->contact_label ?: $conversation->contact_identifier),
            'status' => $conversation->status,
            'status_label' => trans('whatsapp::app.inbox.statuses.'.$conversation->status),
            'status_tone' => $this->statusTone($conversation->status),
            'unread_count' => (int) $conversation->unread_count,
            'is_unread' => $conversation->is_unread,
            'is_pinned' => $conversation->is_pinned,
            'is_archived' => $conversation->is_archived,
            'priority' => $conversation->priority,
            'priority_label' => trans('whatsapp::app.inbox.priorities.'.$conversation->priority),
            'priority_tone' => $this->priorityTone($conversation->priority),
            'follow_up_at' => $conversation->follow_up_at?->format('Y-m-d\\TH:i'),
            'follow_up_label' => $conversation->follow_up_at?->format('M d, Y h:i A'),
            'next_action' => $conversation->next_action,
            'assigned_user_id' => $conversation->assigned_user_id,
            'owner_user_id' => $conversation->owner_user_id,
            'person_id' => $conversation->person_id,
            'lead_id' => $conversation->lead_id,
            'person' => $conversation->person ? [
                'id' => $conversation->person->id,
                'name' => $conversation->person->name,
                'emails' => collect($conversation->person->emails ?? [])->pluck('value')->filter()->values(),
                'contact_numbers' => collect($conversation->person->contact_numbers ?? [])->pluck('value')->filter()->values(),
                'organization' => $conversation->person->organization ? [
                    'id' => $conversation->person->organization->id,
                    'name' => $conversation->person->organization->name,
                    'url' => route('admin.contacts.organizations.edit', $conversation->person->organization->id),
                ] : null,
            ] : null,
            'lead' => $conversation->lead ? [
                'id' => $conversation->lead->id,
                'title' => $conversation->lead->title,
                'stage' => $conversation->lead->stage?->name,
            ] : null,
            'assigned_user' => $this->transformUser($conversation->assignedUser),
            'owner_user' => $this->transformUser($conversation->ownerUser),
            'tags' => $conversation->tags->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->name, 'color' => $tag->color])->values(),
            'last_message_preview' => $conversation->last_message_preview ?: trans('whatsapp::app.inbox.datagrid.no_preview'),
            'contact_url' => $conversation->person_id ? route('admin.contacts.persons.view', $conversation->person_id) : null,
            'lead_url' => $conversation->lead_id ? route('admin.leads.view', $conversation->lead_id) : null,
            'organization' => $conversation->person?->organization ? [
                'id' => $conversation->person->organization->id,
                'name' => $conversation->person->organization->name,
                'url' => route('admin.contacts.organizations.edit', $conversation->person->organization->id),
            ] : null,
        ];
    }

    protected function transformMessage($message): array
    {
        $createdAt = $message->created_at;

        return [
            'id' => $message->id,
            'direction' => $message->direction,
            'direction_label' => $message->direction_label,
            'content' => $message->content,
            'message_type' => $message->message_type,
            'status' => $message->status,
            'status_label' => Str::of((string) $message->status)->replace('_', ' ')->headline()->value(),
            'sender_name' => $message->sender_name,
            'recipient_phone' => $message->recipient_phone,
            'created_at' => $createdAt?->format('M d, Y h:i A'),
            'created_at_iso' => $createdAt?->toIso8601String(),
            'created_at_time' => $createdAt?->format('h:i A'),
            'date_label' => $createdAt?->format('D, M d'),
        ];
    }

    protected function transformActivity($activity): array
    {
        $additional = is_array($activity->additional)
            ? $activity->additional
            : json_decode($activity->additional ?? '[]', true);

        return [
            'id' => $activity->id,
            'title' => $activity->title,
            'type' => $activity->type,
            'comment' => $activity->comment,
            'additional' => $additional ?: [],
            'created_at' => $activity->created_at,
            'created_at_label' => $activity->created_at?->format('M d, Y h:i A'),
            'user' => $this->transformUser($activity->user),
        ];
    }

    protected function transformUser($user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'image_url' => $user->image_url,
            'initials' => $this->initials($user->name),
        ];
    }

    protected function initials(?string $value): string
    {
        $words = collect(preg_split('/\s+/', trim((string) $value)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)));

        return $words->isNotEmpty()
            ? $words->implode('')
            : 'WA';
    }

    protected function statusTone(?string $status): string
    {
        return match ($status) {
            'closed' => 'rose',
            'pending' => 'amber',
            default => 'emerald',
        };
    }

    protected function priorityTone(?string $priority): string
    {
        return match ($priority) {
            'urgent' => 'rose',
            'high' => 'amber',
            'low' => 'slate',
            default => 'sky',
        };
    }

    protected function logFieldChanges(WhatsAppConversation $conversation, array $original, array $payload): void
    {
        if (($original['assigned_user_id'] ?? null) !== ($payload['assigned_user_id'] ?? null)) {
            $this->logActivity($conversation, trans('whatsapp::app.activities.assignment_changed'), [
                'old' => $this->resolveUserName($original['assigned_user_id'] ?? null),
                'new' => $this->resolveUserName($payload['assigned_user_id'] ?? null),
            ]);
        }

        if (($original['owner_user_id'] ?? null) !== ($payload['owner_user_id'] ?? null)) {
            $this->logActivity($conversation, trans('whatsapp::app.activities.owner_changed'), [
                'old' => $this->resolveUserName($original['owner_user_id'] ?? null),
                'new' => $this->resolveUserName($payload['owner_user_id'] ?? null),
            ]);
        }

        if (($original['status'] ?? null) !== ($payload['status'] ?? null)) {
            $this->logActivity($conversation, trans('whatsapp::app.activities.status_changed'), [
                'old' => trans('whatsapp::app.inbox.statuses.'.($original['status'] ?? 'open')),
                'new' => trans('whatsapp::app.inbox.statuses.'.($payload['status'] ?? 'open')),
            ]);
        }

        if (($original['priority'] ?? null) !== ($payload['priority'] ?? null)) {
            $this->logActivity($conversation, trans('whatsapp::app.activities.priority_changed'), [
                'old' => trans('whatsapp::app.inbox.priorities.'.($original['priority'] ?? 'medium')),
                'new' => trans('whatsapp::app.inbox.priorities.'.($payload['priority'] ?? 'medium')),
            ]);
        }
    }

    protected function resolveUserName(?int $userId): ?string
    {
        if (! $userId) {
            return null;
        }

        return optional($this->userRepository->find($userId))->name;
    }

    protected function logActivity(WhatsAppConversation $conversation, string $title, array $additional = [], ?string $comment = null): void
    {
        $activity = $this->activityRepository->create([
            'type' => 'system',
            'title' => $title,
            'comment' => $comment,
            'additional' => $additional ? json_encode($additional) : null,
            'is_done' => 1,
            'user_id' => auth()->guard('user')->id(),
        ]);

        if (! $conversation->activities()->where('activities.id', $activity->id)->exists()) {
            $conversation->activities()->attach($activity->id);
        }
    }
}
