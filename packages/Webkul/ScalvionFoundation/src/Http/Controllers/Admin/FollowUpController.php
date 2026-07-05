<?php

namespace Webkul\ScalvionFoundation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\ScalvionFoundation\Models\FollowUp;
use Webkul\ScalvionFoundation\Models\NotificationPreference;
use Webkul\ScalvionFoundation\Notifications\EntityNotification;
use Webkul\ScalvionFoundation\Support\AuditLogger;
use Webkul\ScalvionFoundation\Support\EntityResolver;
use Webkul\User\Models\User;

class FollowUpController extends Controller
{
    public function __construct(
        protected EntityResolver $entityResolver,
        protected AuditLogger $auditLogger,
    ) {}

    public function index(string $entityType, int $entityId): JsonResponse
    {
        $entity = $this->entityResolver->find($entityType, $entityId);

        return response()->json([
            'data' => FollowUp::query()
                ->where('followupable_type', $entity::class)
                ->where('followupable_id', $entity->getKey())
                ->with(['assignedUser', 'creator', 'completer'])
                ->latest('remind_at')
                ->get()
                ->map(fn (FollowUp $followUp) => $this->format($followUp)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_type'       => ['required', 'string'],
            'entity_id'         => ['required', 'integer'],
            'assigned_user_id'  => ['nullable', 'integer', 'exists:users,id'],
            'title'             => ['required', 'string', 'max:255'],
            'note'              => ['nullable', 'string'],
            'remind_at'         => ['nullable', 'date'],
        ]);

        $entity = $this->entityResolver->find($validated['entity_type'], (int) $validated['entity_id']);

        $followUp = FollowUp::query()->create([
            'followupable_type' => $entity::class,
            'followupable_id'   => $entity->getKey(),
            'assigned_user_id'  => $validated['assigned_user_id'] ?? null,
            'created_by'        => auth()->id(),
            'title'             => $validated['title'],
            'note'              => $validated['note'] ?? null,
            'status'            => 'pending',
            'remind_at'         => $validated['remind_at'] ?? null,
        ]);

        $this->auditLogger->addTimelineEvent($entity, 'follow_up_created', 'Follow-up created', $followUp->title);
        $this->auditLogger->log($entity, 'follow_up_create', [], $followUp->toArray(), ['follow_up_id' => $followUp->id]);

        $this->notifyAssignedUser($entity, $followUp);

        return response()->json([
            'message' => 'Follow-up created successfully.',
            'data'    => $this->format($followUp->load(['assignedUser', 'creator', 'completer'])),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $followUp = FollowUp::query()->with('followupable')->findOrFail($id);

        $validated = $request->validate([
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'title'            => ['required', 'string', 'max:255'],
            'note'             => ['nullable', 'string'],
            'status'           => ['required', 'in:pending,snoozed,completed'],
            'remind_at'        => ['nullable', 'date'],
        ]);

        $before = $followUp->toArray();

        $followUp->update($validated);

        $this->auditLogger->addTimelineEvent($followUp->followupable, 'follow_up_updated', 'Follow-up updated', $followUp->title);
        $this->auditLogger->log($followUp->followupable, 'follow_up_update', $before, $followUp->fresh()->toArray(), ['follow_up_id' => $followUp->id]);

        return response()->json([
            'message' => 'Follow-up updated successfully.',
            'data'    => $this->format($followUp->fresh(['assignedUser', 'creator', 'completer'])),
        ]);
    }

    public function complete(int $id): JsonResponse
    {
        $followUp = FollowUp::query()->with('followupable')->findOrFail($id);

        $followUp->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);

        $this->auditLogger->addTimelineEvent($followUp->followupable, 'follow_up_completed', 'Follow-up completed', $followUp->title);

        return response()->json([
            'message' => 'Follow-up completed.',
            'data'    => $this->format($followUp->fresh(['assignedUser', 'creator', 'completer'])),
        ]);
    }

    public function snooze(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'snoozed_until' => ['required', 'date'],
        ]);

        $followUp = FollowUp::query()->with('followupable')->findOrFail($id);

        $followUp->update([
            'status'        => 'snoozed',
            'snoozed_until' => $validated['snoozed_until'],
        ]);

        $this->auditLogger->addTimelineEvent($followUp->followupable, 'follow_up_snoozed', 'Follow-up snoozed', $followUp->title);

        return response()->json([
            'message' => 'Follow-up snoozed.',
            'data'    => $this->format($followUp->fresh(['assignedUser', 'creator', 'completer'])),
        ]);
    }

    public function overview(): JsonResponse
    {
        $userId = auth()->id();

        $baseQuery = FollowUp::query()->with('assignedUser')
            ->when($userId, fn ($q) => $q->where(function ($q) use ($userId) {
                $q->whereNull('assigned_user_id')->orWhere('assigned_user_id', $userId);
            }));

        $overdue = (clone $baseQuery)
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->where('remind_at', '<', now())->orWhereNull('remind_at');
            })
            ->orderBy('remind_at')
            ->limit(10)
            ->get()
            ->map(fn (FollowUp $f) => $this->format($f));

        $today = (clone $baseQuery)
            ->whereIn('status', ['pending', 'snoozed'])
            ->whereBetween('remind_at', [now()->startOfDay(), now()->endOfDay()])
            ->orderBy('remind_at')
            ->limit(10)
            ->get()
            ->map(fn (FollowUp $f) => $this->format($f));

        $upcoming = (clone $baseQuery)
            ->whereIn('status', ['pending', 'snoozed'])
            ->where(function ($q) {
                $q->where('remind_at', '>', now()->endOfDay())->orWhereNull('remind_at');
            })
            ->orderByRaw('CASE WHEN remind_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('remind_at')
            ->limit(10)
            ->get()
            ->map(fn (FollowUp $f) => $this->format($f));

        $completed = (clone $baseQuery)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->limit(10)
            ->get()
            ->map(fn (FollowUp $f) => $this->format($f));

        return response()->json([
            'overdue'   => $overdue,
            'today'     => $today,
            'upcoming'  => $upcoming,
            'completed' => $completed,
        ]);
    }

    public function upcoming(): JsonResponse
    {
        return response()->json([
            'data' => FollowUp::query()
                ->with('assignedUser')
                ->whereIn('status', ['pending', 'snoozed'])
                ->when(auth()->id(), fn ($query, $userId) => $query->where(function ($subQuery) use ($userId) {
                    $subQuery->whereNull('assigned_user_id')->orWhere('assigned_user_id', $userId);
                }))
                ->orderByRaw('CASE WHEN remind_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('remind_at')
                ->limit(10)
                ->get()
                ->map(fn (FollowUp $followUp) => $this->format($followUp)),
        ]);
    }

    protected function notifyAssignedUser($entity, FollowUp $followUp): void
    {
        if (! $followUp->assigned_user_id || $followUp->assigned_user_id === auth()->id()) {
            return;
        }

        $preference = NotificationPreference::query()->firstOrCreate(
            ['user_id' => $followUp->assigned_user_id],
            ['channels' => ['database']]
        );

        if (! $preference->reminder_notifications) {
            return;
        }

        $user = User::query()->find($followUp->assigned_user_id);

        if (! $user) {
            return;
        }

        $user->notify(new EntityNotification(
            'New follow-up assigned',
            $followUp->title.' was assigned to you.',
            $this->entityUrl($entity),
            [
                'entity_type'  => $this->entityResolver->shortType($entity),
                'entity_id'    => $entity->getKey(),
                'follow_up_id' => $followUp->id,
            ],
        ));
    }

    protected function entityUrl($entity): ?string
    {
        return match ($entity::class) {
            \Webkul\Contact\Models\Person::class       => route('admin.contacts.persons.view', $entity->getKey()),
            \Webkul\Contact\Models\Organization::class => route('admin.contacts.organizations.edit', $entity->getKey()),
            \Webkul\Lead\Models\Lead::class            => route('admin.leads.view', $entity->getKey()),
            \Webkul\WhatsApp\Models\WhatsAppConversation::class => route('admin.whatsapp.inbox.view', $entity->getKey()),
            default                                    => null,
        };
    }

    protected function format(FollowUp $followUp): array
    {
        return [
            'id'            => $followUp->id,
            'title'         => $followUp->title,
            'note'          => $followUp->note,
            'status'        => $followUp->status,
            'remind_at'     => optional($followUp->remind_at)?->toIso8601String(),
            'snoozed_until' => optional($followUp->snoozed_until)?->toIso8601String(),
            'completed_at'  => optional($followUp->completed_at)?->toIso8601String(),
            'assigned_user' => $followUp->assignedUser ? [
                'id'   => $followUp->assignedUser->id,
                'name' => $followUp->assignedUser->name,
            ] : null,
            'creator'       => $followUp->creator ? [
                'id'   => $followUp->creator->id,
                'name' => $followUp->creator->name,
            ] : null,
        ];
    }
}
