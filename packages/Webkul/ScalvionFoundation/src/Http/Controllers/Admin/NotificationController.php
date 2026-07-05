<?php

namespace Webkul\ScalvionFoundation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\ScalvionFoundation\Models\NotificationPreference;

class NotificationController extends Controller
{
    public function summary(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'unread'       => $user->unreadNotifications()->latest()->limit(10)->get()->map(fn ($notification) => $this->format($notification)),
            'history'      => $user->notifications()->latest()->limit(20)->get()->map(fn ($notification) => $this->format($notification)),
        ]);
    }

    public function unread(): JsonResponse
    {
        return response()->json([
            'data' => auth()->user()->unreadNotifications()->latest()->get()->map(fn ($notification) => $this->format($notification)),
        ]);
    }

    public function markRead(string $id): JsonResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function history(Request $request): View|JsonResponse
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(25);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => collect($notifications->items())->map(fn ($notification) => $this->format($notification)),
                'meta' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page'    => $notifications->lastPage(),
                    'total'        => $notifications->total(),
                ],
            ]);
        }

        return view('scalvion-foundation::admin.notifications.history', compact('notifications'));
    }

    public function preferences(): View
    {
        $preference = NotificationPreference::query()->firstOrCreate(
            ['user_id' => auth()->id()],
            ['channels' => ['database']]
        );

        return view('scalvion-foundation::admin.notifications.preferences', compact('preference'));
    }

    public function storePreferences(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'reminder_notifications' => ['nullable', 'boolean'],
            'activity_notifications' => ['nullable', 'boolean'],
            'audit_notifications'    => ['nullable', 'boolean'],
            'whatsapp_notifications' => ['nullable', 'boolean'],
            'channels'               => ['nullable', 'array'],
        ]);

        $preference = NotificationPreference::query()->updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'reminder_notifications' => (bool) ($validated['reminder_notifications'] ?? false),
                'activity_notifications' => (bool) ($validated['activity_notifications'] ?? false),
                'audit_notifications'    => (bool) ($validated['audit_notifications'] ?? false),
                'whatsapp_notifications' => (bool) ($validated['whatsapp_notifications'] ?? false),
                'channels'               => $validated['channels'] ?? ['database'],
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Notification preferences saved.',
                'data'    => $preference,
            ]);
        }

        return redirect()
            ->route('admin.scalvion.notifications.preferences')
            ->with('success', 'Notification preferences saved.');
    }

    protected function format($notification): array
    {
        return [
            'id'         => $notification->id,
            'title'      => data_get($notification->data, 'title'),
            'body'       => data_get($notification->data, 'body'),
            'action_url' => data_get($notification->data, 'action_url'),
            'meta'       => data_get($notification->data, 'meta', []),
            'read_at'    => optional($notification->read_at)?->toIso8601String(),
            'created_at' => optional($notification->created_at)?->toIso8601String(),
        ];
    }
}
