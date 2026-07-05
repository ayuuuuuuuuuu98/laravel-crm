<?php

namespace Webkul\WhatsApp\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Source;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Models\Type;
use Webkul\Tag\Models\Tag;
use Webkul\User\Models\User;
use Webkul\WhatsApp\Models\WhatsAppAccount;
use Webkul\WhatsApp\Models\WhatsAppConversation;
use Webkul\WhatsApp\Models\WhatsAppMessage;

class WhatsAppDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('status', 1)->orderBy('id')->first();

        if (! $user) {
            $this->command?->warn('No active CRM user found. Demo seeding skipped.');

            return;
        }

        $account = $this->ensureAccount($user);
        $person = $this->ensurePerson($user);
        $lead = $this->createLeadIfPossible($person, $user);
        $tags = $this->ensureTags($user);
        $definitions = $this->conversationDefinitions($person, $lead, $user);

        $createdCount = 0;
        $messageCount = 0;

        foreach ($definitions as $definition) {
            [$conversation, $created] = $this->ensureConversation($account, $definition);

            if ($created) {
                $createdCount++;
            }

            $messageCount += $this->syncConversationMessages($conversation, $account, $definition['messages']);
            $this->syncConversationTags($conversation, $tags, $definition['tags']);
        }

        $messageCount += $this->backfillEmptyConversations($account);

        $this->command?->info('WhatsApp demo seeding completed.');
        $this->command?->line('Conversations ensured: '.count($definitions).'. Newly created: '.$createdCount.'.');
        $this->command?->line('Messages added or repaired: '.$messageCount.'.');
    }

    protected function ensureAccount(User $user): WhatsAppAccount
    {
        return WhatsAppAccount::query()->firstOrCreate(
            ['name' => 'Demo WhatsApp Workspace'],
            [
                'provider'            => 'meta',
                'phone_number_id'     => 'DEMO-PHONE-001',
                'business_account_id' => 'DEMO-BUSINESS-001',
                'access_token'        => 'demo-access-token',
                'verify_token'        => 'demo-verify-token',
                'status'              => 'active',
                'settings'            => ['demo' => true],
                'user_id'             => $user->id,
            ]
        );
    }

    protected function ensurePerson(User $user): Person
    {
        $organization = Organization::query()->firstOrCreate(
            ['name' => 'Scalvion Demo Co'],
            [
                'user_id' => $user->id,
                'address' => [
                    'city'    => 'Bengaluru',
                    'country' => 'India',
                ],
            ]
        );

        $person = Person::query()
            ->where('contact_numbers', 'like', '%+91 98765 43210%')
            ->orWhere('emails', 'like', '%ananya.patel@example.com%')
            ->orderBy('id')
            ->first();

        if ($person) {
            return $person;
        }

        return Person::query()->create([
            'name'            => 'Ananya Patel',
            'emails'          => [['label' => 'work', 'value' => 'ananya.patel@example.com']],
            'contact_numbers' => [['label' => 'mobile', 'value' => '+91 98765 43210']],
            'job_title'       => 'Operations Manager',
            'user_id'         => $user->id,
            'organization_id' => $organization->id,
        ]);
    }

    protected function ensureTags(User $user)
    {
        $tagNames = ['VIP', 'Renewal', 'Support'];

        return collect($tagNames)->mapWithKeys(function (string $name) use ($user) {
            $tag = Tag::query()->firstOrCreate(
                ['name' => $name],
                [
                    'color'   => match ($name) {
                        'VIP' => '#f97316',
                        'Renewal' => '#0ea5e9',
                        default => '#10b981',
                    },
                    'user_id' => $user->id,
                ]
            );

            return [$name => $tag];
        });
    }

    protected function conversationDefinitions(Person $person, ?Lead $lead, User $user): array
    {
        return [
            [
                'contact_identifier' => '+91 98765 43210',
                'person_id'          => $person->id,
                'lead_id'            => $lead?->id,
                'assigned_user_id'   => $user->id,
                'owner_user_id'      => $user->id,
                'status'             => 'open',
                'priority'           => 'high',
                'unread_count'       => 2,
                'follow_up_at'       => Carbon::now()->addDay(),
                'next_action'        => 'Share enterprise pricing and onboarding slots',
                'archived_at'        => null,
                'pinned_at'          => Carbon::now()->subHours(2),
                'tags'               => ['VIP', 'Renewal'],
                'messages'           => [
                    ['external_message_id' => 'demo-scalvion-1', 'direction' => 'inbound', 'content' => 'Hi, we are comparing WhatsApp CRM options for our support team.', 'sender_name' => 'Ananya Patel', 'status' => 'delivered', 'created_at' => Carbon::now()->subHours(26)],
                    ['external_message_id' => 'demo-scalvion-2', 'direction' => 'outbound', 'content' => 'Happy to help. How many agents and active chats are you planning for?', 'sender_name' => 'Scalvion Sales', 'status' => 'read', 'created_at' => Carbon::now()->subHours(25)->addMinutes(10)],
                    ['external_message_id' => 'demo-scalvion-3', 'direction' => 'inbound', 'content' => 'We expect about 12 agents and want routing, notes, and lead linking.', 'sender_name' => 'Ananya Patel', 'status' => 'delivered', 'created_at' => Carbon::now()->subHours(24)->addMinutes(5)],
                    ['external_message_id' => 'demo-scalvion-4', 'direction' => 'outbound', 'content' => 'Perfect. I have linked your CRM record and will send enterprise pricing next.', 'sender_name' => 'Scalvion Sales', 'status' => 'sent', 'created_at' => Carbon::now()->subHours(23)->addMinutes(20)],
                ],
            ],
            [
                'contact_identifier' => '+91 99887 77665',
                'person_id'          => null,
                'lead_id'            => null,
                'assigned_user_id'   => null,
                'owner_user_id'      => null,
                'status'             => 'pending',
                'priority'           => 'medium',
                'unread_count'       => 0,
                'follow_up_at'       => Carbon::now()->addHours(6),
                'next_action'        => 'Confirm deployment checklist',
                'archived_at'        => null,
                'pinned_at'          => null,
                'tags'               => ['Support'],
                'messages'           => [
                    ['external_message_id' => 'demo-scalvion-5', 'direction' => 'inbound', 'content' => 'Can you confirm if internal notes are visible to customers?', 'sender_name' => 'Raghav Menon', 'status' => 'delivered', 'created_at' => Carbon::now()->subHours(11)],
                    ['external_message_id' => 'demo-scalvion-6', 'direction' => 'outbound', 'content' => 'Internal notes stay private inside Krayin and are never sent to WhatsApp.', 'sender_name' => 'Scalvion Support', 'status' => 'read', 'created_at' => Carbon::now()->subHours(10)->addMinutes(12)],
                    ['external_message_id' => 'demo-scalvion-7', 'direction' => 'inbound', 'content' => 'Great. We also need assignment and label filters for supervisors.', 'sender_name' => 'Raghav Menon', 'status' => 'delivered', 'created_at' => Carbon::now()->subHours(9)->addMinutes(10)],
                ],
            ],
            [
                'contact_identifier' => '+91 91234 56789',
                'person_id'          => null,
                'lead_id'            => null,
                'assigned_user_id'   => $user->id,
                'owner_user_id'      => $user->id,
                'status'             => 'closed',
                'priority'           => 'low',
                'unread_count'       => 1,
                'follow_up_at'       => null,
                'next_action'        => 'Closed after plan confirmation',
                'archived_at'        => Carbon::now()->subDay(),
                'pinned_at'          => null,
                'tags'               => ['Support'],
                'messages'           => [
                    ['external_message_id' => 'demo-scalvion-8', 'direction' => 'inbound', 'content' => 'Thanks, the pilot looks good. Please close this thread for now.', 'sender_name' => 'Nisha Rao', 'status' => 'delivered', 'created_at' => Carbon::now()->subDays(2)->addHours(3)],
                    ['external_message_id' => 'demo-scalvion-9', 'direction' => 'outbound', 'content' => 'Done. We can reopen anytime if your team needs another walkthrough.', 'sender_name' => 'Scalvion Success', 'status' => 'read', 'created_at' => Carbon::now()->subDays(2)->addHours(3)->addMinutes(18)],
                    ['external_message_id' => 'demo-scalvion-10', 'direction' => 'inbound', 'content' => 'Please keep the documentation link handy for our weekend review.', 'sender_name' => 'Nisha Rao', 'status' => 'delivered', 'created_at' => Carbon::now()->subDays(2)->addHours(4)],
                ],
            ],
        ];
    }

    protected function ensureConversation(WhatsAppAccount $account, array $definition): array
    {
        $messageTimes = collect($definition['messages'])->pluck('created_at')->filter();
        $lastMessageAt = $messageTimes->max();

        $conversation = WhatsAppConversation::query()
            ->where('account_id', $account->id)
            ->where('contact_identifier', $definition['contact_identifier'])
            ->first();

        $created = false;

        if (! $conversation) {
            $conversation = WhatsAppConversation::query()->create([
                'account_id'         => $account->id,
                'contact_identifier' => $definition['contact_identifier'],
                'person_id'          => $definition['person_id'],
                'lead_id'            => $definition['lead_id'],
                'assigned_user_id'   => $definition['assigned_user_id'],
                'owner_user_id'      => $definition['owner_user_id'],
                'last_message_at'    => $lastMessageAt,
                'unread_count'       => $definition['unread_count'],
                'status'             => $definition['status'],
                'priority'           => $definition['priority'],
                'next_action'        => $definition['next_action'],
                'follow_up_at'       => $definition['follow_up_at'],
                'pinned_at'          => $definition['pinned_at'],
                'archived_at'        => $definition['archived_at'],
                'metadata'           => ['demo' => true],
            ]);

            $created = true;
        } else {
            $payload = [
                'last_message_at' => $lastMessageAt,
            ];

            foreach ([
                'person_id',
                'lead_id',
                'assigned_user_id',
                'owner_user_id',
                'priority',
                'next_action',
                'follow_up_at',
                'pinned_at',
                'archived_at',
            ] as $field) {
                if (is_null($conversation->{$field}) && ! is_null($definition[$field])) {
                    $payload[$field] = $definition[$field];
                }
            }

            if ((int) $conversation->unread_count === 0 && (int) $definition['unread_count'] > 0 && ! $conversation->messages()->exists()) {
                $payload['unread_count'] = $definition['unread_count'];
            }

            if (! $conversation->status && ! empty($definition['status'])) {
                $payload['status'] = $definition['status'];
            }

            $conversation->update($payload);
        }

        return [$conversation->fresh(), $created];
    }

    protected function syncConversationMessages(WhatsAppConversation $conversation, WhatsAppAccount $account, array $messages): int
    {
        $created = 0;

        foreach ($messages as $message) {
            $existing = WhatsAppMessage::query()
                ->where('external_message_id', $message['external_message_id'])
                ->where('conversation_id', $conversation->id)
                ->first();

            if ($existing) {
                continue;
            }

            WhatsAppMessage::query()->create([
                'conversation_id'     => $conversation->id,
                'account_id'          => $account->id,
                'direction'           => $message['direction'],
                'message_type'        => 'text',
                'content'             => $message['content'],
                'external_message_id' => $message['external_message_id'],
                'status'              => $message['status'],
                'sender_name'         => $message['sender_name'],
                'recipient_phone'     => $conversation->contact_identifier,
                'metadata'            => ['demo' => true],
                'created_at'          => $message['created_at'],
                'updated_at'          => $message['created_at'],
            ]);

            $created++;
        }

        return $created;
    }

    protected function syncConversationTags(WhatsAppConversation $conversation, $tags, array $tagNames): void
    {
        $attachTags = collect($tagNames)
            ->map(fn (string $name) => optional($tags->get($name))->id)
            ->filter()
            ->values()
            ->all();

        if ($attachTags) {
            $conversation->tags()->syncWithoutDetaching($attachTags);
        }
    }

    protected function backfillEmptyConversations(WhatsAppAccount $account): int
    {
        $added = 0;

        $conversations = WhatsAppConversation::query()
            ->whereDoesntHave('messages')
            ->orderBy('id')
            ->get();

        foreach ($conversations as $conversation) {
            $transcript = $this->fallbackTranscriptFor($conversation);

            foreach ($transcript as $index => $message) {
                WhatsAppMessage::query()->create([
                    'conversation_id'     => $conversation->id,
                    'account_id'          => $conversation->account_id ?: $account->id,
                    'direction'           => $message['direction'],
                    'message_type'        => 'text',
                    'content'             => $message['content'],
                    'external_message_id' => 'demo-backfill-'.$conversation->id.'-'.($index + 1),
                    'status'              => $message['status'],
                    'sender_name'         => $message['sender_name'],
                    'recipient_phone'     => $conversation->contact_identifier,
                    'metadata'            => ['demo' => true, 'backfill' => true],
                    'created_at'          => $message['created_at'],
                    'updated_at'          => $message['created_at'],
                ]);

                $added++;
            }

            $conversation->update([
                'last_message_at' => collect($transcript)->pluck('created_at')->max(),
                'unread_count' => max((int) $conversation->unread_count, 1),
            ]);
        }

        return $added;
    }

    protected function fallbackTranscriptFor(WhatsAppConversation $conversation): array
    {
        $label = $conversation->contact_identifier ?: 'customer';

        return [
            [
                'direction' => 'inbound',
                'content' => 'Hi, I am reaching out from '.$label.' and need an update in the CRM workspace.',
                'sender_name' => $conversation->contact_label ?: 'WhatsApp Contact',
                'status' => 'delivered',
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'direction' => 'outbound',
                'content' => 'Thanks, this thread has been linked locally so your team can continue inside the inbox.',
                'sender_name' => 'Scalvion CRM',
                'status' => 'sent',
                'created_at' => Carbon::now()->subHours(2)->addMinutes(10),
            ],
            [
                'direction' => 'inbound',
                'content' => 'Perfect, please keep this conversation visible for assignment and follow-up.',
                'sender_name' => $conversation->contact_label ?: 'WhatsApp Contact',
                'status' => 'delivered',
                'created_at' => Carbon::now()->subHour(),
            ],
        ];
    }

    protected function createLeadIfPossible(Person $person, User $user): ?Lead
    {
        $existingLead = Lead::query()
            ->where('person_id', $person->id)
            ->where('title', 'WhatsApp Enterprise Evaluation')
            ->orderBy('id')
            ->first();

        if ($existingLead) {
            return $existingLead;
        }

        $source = Source::query()->orderBy('id')->first();
        $type = Type::query()->orderBy('id')->first();
        $pipeline = Pipeline::query()->orderBy('id')->first();
        $stage = $pipeline?->stages()->orderBy('sort_order')->first() ?: Stage::query()->orderBy('id')->first();

        if (! $source || ! $type) {
            return null;
        }

        return Lead::query()->create([
            'title'                  => 'WhatsApp Enterprise Evaluation',
            'description'            => 'Demo lead linked from the WhatsApp inbox workspace.',
            'lead_value'             => 50000,
            'status'                 => 1,
            'user_id'                => $user->id,
            'person_id'              => $person->id,
            'lead_source_id'         => $source->id,
            'lead_type_id'           => $type->id,
            'lead_pipeline_id'       => $pipeline?->id,
            'lead_pipeline_stage_id' => $stage?->id,
        ]);
    }
}
