<v-scalvion-notification-center>
    <button
        type="button"
        class="fixed right-4 top-[74px] z-[10020] flex h-11 w-11 items-center justify-center rounded-full border border-gray-300 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
    >
        <span class="icon-mail text-xl"></span>
    </button>
</v-scalvion-notification-center>

@pushOnce('scripts')
    <script type="text/x-template" id="v-scalvion-notification-center-template">
        <div class="fixed right-4 top-[74px] z-[10020]">
            <button
                type="button"
                class="relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-300 bg-white shadow-sm transition hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900"
                @click="isOpen = ! isOpen; if (isOpen) load();"
            >
                <span class="icon-mail text-xl dark:text-white"></span>

                <span
                    v-if="unreadCount"
                    class="absolute -right-1 -top-1 inline-flex min-w-[20px] items-center justify-center rounded-full bg-brandColor px-1.5 py-0.5 text-[11px] font-semibold text-white"
                >
                    @{{ unreadCount }}
                </span>
            </button>

            <div
                v-if="isOpen"
                class="mt-3 w-[360px] max-w-[calc(100vw-2rem)] rounded-2xl border border-gray-300 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                    <div>
                        <p class="font-semibold dark:text-white">Notifications</p>
                        <p class="text-xs text-gray-500">Assignments, follow-ups, and activity alerts.</p>
                    </div>

                    <button type="button" class="text-xs font-semibold text-brandColor" @click="markAllRead">
                        Mark all read
                    </button>
                </div>

                <div class="max-h-[420px] overflow-y-auto">
                    <div v-if="isLoading" class="space-y-3 p-4">
                        <div v-for="item in 4" :key="item" class="animate-pulse rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                            <div class="mb-2 h-4 w-1/2 rounded bg-gray-200 dark:bg-gray-800"></div>
                            <div class="h-3 w-full rounded bg-gray-200 dark:bg-gray-800"></div>
                        </div>
                    </div>

                    <div v-else-if="! history.length" class="p-6 text-center text-sm text-gray-500">
                        No notifications yet.
                    </div>

                    <div v-else class="divide-y divide-gray-200 dark:divide-gray-800">
                        <div
                            v-for="notification in history"
                            :key="notification.id"
                            class="flex cursor-pointer items-start justify-between gap-3 p-4 transition hover:bg-gray-50 dark:hover:bg-gray-950"
                            :class="{ 'bg-blue-50/40 dark:bg-blue-950/20': ! notification.read_at }"
                            @click="navigate(notification)"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold dark:text-white">@{{ notification.title }}</p>
                                <p class="mt-1 text-sm text-gray-500">@{{ notification.body }}</p>
                                <p class="mt-1 text-xs text-gray-400">@{{ formatDate(notification.created_at) }}</p>
                            </div>

                            <button
                                v-if="! notification.read_at"
                                type="button"
                                class="shrink-0 text-[11px] font-semibold text-brandColor hover:underline"
                                @click.stop="markRead(notification.id)"
                            >
                                Mark read
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-xs dark:border-gray-800">
                    <a href="{{ route('admin.scalvion.notifications.history') }}" class="font-semibold text-brandColor">
                        View history
                    </a>

                    <a href="{{ route('admin.scalvion.notifications.preferences') }}" class="font-semibold text-brandColor">
                        Preferences
                    </a>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-scalvion-notification-center', {
            template: '#v-scalvion-notification-center-template',

            data() {
                return {
                    isOpen: false,
                    isLoading: false,
                    unreadCount: 0,
                    history: [],
                };
            },

            mounted() {
                this.load();
            },

            methods: {
                load() {
                    this.isLoading = true;

                    this.$axios.get('{{ route('admin.scalvion.notifications.summary') }}')
                        .then((response) => {
                            this.unreadCount = response.data.unread_count || 0;
                            this.history = response.data.history || [];
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                },

                markRead(id) {
                    this.$axios.post('{{ route('admin.scalvion.notifications.mark_read', '__id__') }}'.replace('__id__', id))
                        .then(() => this.load());
                },

                markAllRead() {
                    this.$axios.post('{{ route('admin.scalvion.notifications.mark_all_read') }}')
                        .then(() => this.load());
                },

                navigate(notification) {
                    if (notification.action_url) {
                        window.location.href = notification.action_url;
                    }
                },

                formatDate(value) {
                    if (! value) {
                        return '';
                    }

                    return new Date(value).toLocaleString();
                },
            },
        });
    </script>
@endPushOnce
