<x-admin::layouts>
    <x-slot:title>
        Audit Logs
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] rounded-lg border border-gray-300 bg-white px-4 py-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h1 class="text-xl font-bold dark:text-white">Audit Logs</h1>
            <p class="text-sm text-gray-500">Create, update, delete, assignment, and status changes.</p>
        </div>

        <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="space-y-3">
                @forelse ($logs as $log)
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold capitalize dark:text-white">{{ $log->action }}</p>
                                <p class="text-xs text-gray-500">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</p>
                            </div>
                            <span class="text-xs text-gray-400">{{ $log->created_at?->format('d M Y h:i A') }}</span>
                        </div>
                        <pre class="mt-3 overflow-x-auto rounded-lg bg-gray-50 p-3 text-[11px] text-gray-600 dark:bg-gray-950 dark:text-gray-300">{{ json_encode(['before' => $log->before, 'after' => $log->after], JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700">
                        No audit logs yet.
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-admin::layouts>
