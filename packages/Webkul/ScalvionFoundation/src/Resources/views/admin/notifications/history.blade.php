<x-admin::layouts>
    <x-slot:title>
        Notification History
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div>
                <h1 class="text-xl font-bold dark:text-white">Notification History</h1>
                <p class="text-sm text-gray-500">Unread and read alerts across Sprint 2 modules.</p>
            </div>

            <a href="{{ route('admin.scalvion.notifications.preferences') }}" class="secondary-button">
                Preferences
            </a>
        </div>

        <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="space-y-3">
                @forelse ($notifications as $notification)
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold dark:text-white">{{ data_get($notification->data, 'title') }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ data_get($notification->data, 'body') }}</p>
                            </div>

                            <span class="text-xs text-gray-400">{{ $notification->created_at?->format('d M Y h:i A') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700">
                        No notification history yet.
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-admin::layouts>
