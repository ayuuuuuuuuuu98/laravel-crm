<?php

namespace Webkul\WhatsApp\Repositories;

use Illuminate\Support\Str;
use Webkul\WhatsApp\Models\WhatsAppConversation;
use Webkul\WhatsApp\Models\WhatsAppMessage;
use Webkul\Core\Eloquent\Repository;

class WhatsAppMessageRepository extends Repository
{
    public function model()
    {
        return \Webkul\WhatsApp\Models\WhatsAppMessage::class;
    }

    public function getThreadForConversation(int $conversationId, int $limit = 50, ?int $beforeId = null): array
    {
        $query = WhatsAppMessage::query()
            ->where('conversation_id', $conversationId);

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit + 1)
            ->get();

        $hasMore = $messages->count() > $limit;

        $messages = $messages
            ->take($limit)
            ->sortBy('created_at')
            ->values();

        return [
            'messages' => $messages,
            'has_more' => $hasMore,
            'next_before_id' => $messages->first()?->id,
        ];
    }

    public function getLatestPreviewQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return WhatsAppMessage::query()
            ->select('content')
            ->whereColumn('conversation_id', 'whatsapp_conversations.id')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(1);
    }

    public function getLatestMessageDateQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return WhatsAppMessage::query()
            ->select('created_at')
            ->whereColumn('conversation_id', 'whatsapp_conversations.id')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(1);
    }

    public function getLatestMessageSearchQuery(string $search): \Illuminate\Database\Eloquent\Builder
    {
        return WhatsAppMessage::query()
            ->selectRaw('1')
            ->whereColumn('conversation_id', 'whatsapp_conversations.id')
            ->where('content', 'like', '%'.$search.'%')
            ->limit(1);
    }

    public function createLocalOutboundMessage(WhatsAppConversation $conversation, string $content): WhatsAppMessage
    {
        $timestamp = now();

        $message = WhatsAppMessage::query()->create([
            'conversation_id'     => $conversation->id,
            'account_id'          => $conversation->account_id,
            'direction'           => 'outbound',
            'message_type'        => 'text',
            'content'             => $content,
            'external_message_id' => 'local-'.Str::uuid(),
            'status'              => 'queued',
            'sender_name'         => optional(auth()->guard('user')->user())->name ?: 'CRM Agent',
            'recipient_phone'     => $conversation->contact_identifier,
            'metadata'            => [
                'local_only' => true,
                'source'     => 'crm_inbox',
            ],
            'created_at'          => $timestamp,
            'updated_at'          => $timestamp,
        ]);

        $conversation->update([
            'last_message_at' => $timestamp,
        ]);

        return $message;
    }
}
