<?php

namespace Webkul\WhatsApp\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Tag\Repositories\TagRepository;
use Webkul\User\Repositories\UserRepository;
use Webkul\WhatsApp\DataGrids\WhatsAppConversationDataGrid;
use Webkul\WhatsApp\Repositories\WhatsAppConversationRepository;

class InboxController extends Controller
{
    public function __construct(
        protected WhatsAppConversationRepository $conversationRepository,
        protected UserRepository $userRepository,
        protected TagRepository $tagRepository,
    ) {}

    /**
     * Display the WhatsApp inbox page.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datagrid(WhatsAppConversationDataGrid::class)->process();
        }

        $users = $this->userRepository->all(['id', 'name', 'image']);
        $tags = $this->tagRepository->all(['id', 'name', 'color']);

        return view('whatsapp::admin.inbox.index', [
            'selectedConversationId' => $request->integer('conversation'),
            'users' => $users,
            'tags' => $tags,
        ]);
    }

    public function view(int $id): View
    {
        return view('whatsapp::admin.inbox.view', [
            'selectedConversationId' => $id,
            'users' => $this->userRepository->all(['id', 'name', 'image']),
            'tags' => $this->tagRepository->all(['id', 'name', 'color']),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        return response()->json($this->conversationRepository->getInboxPayload(
            $request->all(),
            (int) $request->input('per_page', 20),
        ));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json($this->conversationRepository->getConversationPayload(
            $id,
            (int) $request->input('limit', 40),
            $request->integer('before_id') ?: null,
        ));
    }

    public function createContact(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);
        $person = $this->conversationRepository->createContactFromConversation($conversation);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.detail.create_contact_success', ['id' => $person->id]),
            'data' => $this->conversationRepository->getConversationPayload($id),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'person_id'         => ['nullable', 'integer', 'exists:persons,id'],
            'lead_id'           => ['nullable', 'integer', 'exists:leads,id'],
            'assigned_user_id'  => ['nullable', 'integer', 'exists:users,id'],
            'owner_user_id'     => ['nullable', 'integer', 'exists:users,id'],
            'status'            => ['required', 'in:open,pending,closed'],
            'priority'          => ['required', 'in:low,medium,high,urgent'],
            'follow_up_at'      => ['nullable', 'date'],
            'next_action'       => ['nullable', 'string', 'max:255'],
        ]);

        $conversation = $this->conversationRepository->findForView($id);

        $this->conversationRepository->updateConversation($conversation, $validated);

        return redirect()
            ->route('admin.whatsapp.inbox.view', $id)
            ->with('success', trans('whatsapp::app.inbox.detail.update_success'));
    }

    public function save(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'person_id'         => ['nullable', 'integer', 'exists:persons,id'],
            'lead_id'           => ['nullable', 'integer', 'exists:leads,id'],
            'assigned_user_id'  => ['nullable', 'integer', 'exists:users,id'],
            'owner_user_id'     => ['nullable', 'integer', 'exists:users,id'],
            'status'            => ['required', 'in:open,pending,closed'],
            'priority'          => ['required', 'in:low,medium,high,urgent'],
            'follow_up_at'      => ['nullable', 'date'],
            'next_action'       => ['nullable', 'string', 'max:255'],
        ]);

        $conversation = $this->conversationRepository->findForView($id);
        $this->conversationRepository->updateConversation($conversation, $validated);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.detail.update_success'),
            'data' => $this->conversationRepository->getConversationPayload($id),
        ]);
    }

    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $conversation = $this->conversationRepository->findForView($id);

        $this->conversationRepository->sendLocalMessage($conversation, trim($validated['content']));

        return response()->json([
            'message' => trans('whatsapp::app.inbox.detail.send_success'),
            'data' => $this->conversationRepository->getConversationPayload($id),
        ]);
    }

    public function createLead(int $id): RedirectResponse
    {
        $conversation = $this->conversationRepository->findForView($id);

        $lead = $this->conversationRepository->createLeadFromConversation($conversation);

        return redirect()
            ->route('admin.whatsapp.inbox.view', $id)
            ->with('success', trans('whatsapp::app.inbox.detail.create_lead_success', ['id' => $lead->id]));
    }

    public function createLeadAjax(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);
        $lead = $this->conversationRepository->createLeadFromConversation($conversation);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.detail.create_lead_success', ['id' => $lead->id]),
            'data' => $this->conversationRepository->getConversationPayload($id),
        ]);
    }

    public function storeNote(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => ['required', 'string'],
        ]);

        $conversation = $this->conversationRepository->findForView($id);

        $this->conversationRepository->createInternalNote($conversation, $validated['comment']);

        return redirect()
            ->route('admin.whatsapp.inbox.view', $id)
            ->with('success', trans('whatsapp::app.inbox.detail.note_success'));
    }

    public function storeNoteAjax(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'comment' => ['required', 'string'],
        ]);

        $conversation = $this->conversationRepository->findForView($id);
        $this->conversationRepository->createInternalNote($conversation, $validated['comment']);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.detail.note_success'),
        ]);
    }

    public function markRead(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);
        $this->conversationRepository->markAsRead($conversation);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.actions.mark_read_success'),
            'data' => $this->conversationRepository->getConversationPayload($id),
        ]);
    }

    public function markUnread(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);
        $this->conversationRepository->markAsUnread($conversation);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.actions.mark_unread_success'),
            'data' => $this->conversationRepository->getConversationPayload($id),
        ]);
    }

    public function archive(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);
        $this->conversationRepository->archive($conversation);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.actions.archive_success'),
        ]);
    }

    public function unarchive(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);
        $this->conversationRepository->unarchive($conversation);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.actions.unarchive_success'),
            'data' => $this->conversationRepository->getConversationPayload($id),
        ]);
    }

    public function pin(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);
        $this->conversationRepository->pin($conversation);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.actions.pin_success'),
            'data' => $this->conversationRepository->getConversationPayload($id),
        ]);
    }

    public function unpin(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);
        $this->conversationRepository->unpin($conversation);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.actions.unpin_success'),
            'data' => $this->conversationRepository->getConversationPayload($id),
        ]);
    }

    public function close(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);
        $this->conversationRepository->close($conversation);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.actions.close_success'),
            'data' => $this->conversationRepository->getConversationPayload($id),
        ]);
    }

    public function reopen(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);
        $this->conversationRepository->reopen($conversation);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.actions.reopen_success'),
            'data' => $this->conversationRepository->getConversationPayload($id),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findForView($id);
        $this->conversationRepository->softDelete($conversation);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.actions.delete_success'),
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $conversation = $this->conversationRepository->restoreConversation($id);

        return response()->json([
            'message' => trans('whatsapp::app.inbox.actions.restore_success'),
            'data' => $this->conversationRepository->getConversationPayload($conversation->id),
        ]);
    }
}
