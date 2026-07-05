<?php

namespace Webkul\WhatsApp\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\WhatsApp\Repositories\WhatsAppConversationRepository;

class TagController extends Controller
{
    public function __construct(protected WhatsAppConversationRepository $conversationRepository) {}

    public function attach(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);

        if (! $conversation->tags->contains(request()->input('tag_id'))) {
            $conversation->tags()->attach(request()->input('tag_id'));
        }

        return response()->json([
            'message' => trans('whatsapp::app.inbox.detail.tags.attach_success'),
        ]);
    }

    public function detach(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);

        $conversation->tags()->detach(request()->input('tag_id'));

        return response()->json([
            'message' => trans('whatsapp::app.inbox.detail.tags.detach_success'),
        ]);
    }
}
