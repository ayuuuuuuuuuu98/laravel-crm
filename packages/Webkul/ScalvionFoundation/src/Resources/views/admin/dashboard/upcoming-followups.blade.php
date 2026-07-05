<v-scalvion-upcoming-followups>
    <div class="box-shadow rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="light-shimmer-bg dark:shimmer h-5 w-40 rounded"></div>
    </div>
</v-scalvion-upcoming-followups>

@pushOnce('scripts')
    <script type="text/x-template" id="v-scalvion-upcoming-followups-template">
        <div class="box-shadow rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold dark:text-white">Follow-ups</h3>
                    <p class="text-xs text-gray-500">Overdue, today, upcoming, and completed reminders.</p>
                </div>
            </div>

            <div v-if="isLoading" class="space-y-3">
                <div v-for="item in 4" :key="item" class="animate-pulse rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                    <div class="mb-2 h-4 w-1/2 rounded bg-gray-200 dark:bg-gray-800"></div>
                    <div class="h-3 w-full rounded bg-gray-200 dark:bg-gray-800"></div>
                </div>
            </div>

            <template v-else>
                <div v-if="overdue.length" class="mb-4">
                    <h4 class="mb-2 text-sm font-semibold text-red-500">Overdue</h4>
                    <div class="space-y-2">
                        <div v-for="item in overdue" :key="'overdue-'+item.id" class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold dark:text-white">@{{ item.title }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">@{{ item.assigned_user ? item.assigned_user.name : 'Unassigned' }}</p>
                                    <p class="mt-0.5 text-xs text-gray-400">@{{ formatDate(item.remind_at) }}</p>
                                </div>
                                <div class="flex shrink-0 gap-1">
                                    <button type="button" class="rounded bg-white px-2 py-1 text-[11px] font-semibold shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700" @click="completeFollowUp(item.id)">Done</button>
                                    <button type="button" class="rounded bg-white px-2 py-1 text-[11px] font-semibold shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700" @click="snoozeFollowUp(item.id)">Snooze</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="today.length" class="mb-4">
                    <h4 class="mb-2 text-sm font-semibold text-amber-500">Today</h4>
                    <div class="space-y-2">
                        <div v-for="item in today" :key="'today-'+item.id" class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-900 dark:bg-amber-950">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold dark:text-white">@{{ item.title }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">@{{ item.assigned_user ? item.assigned_user.name : 'Unassigned' }}</p>
                                    <p class="mt-0.5 text-xs text-gray-400">@{{ formatDate(item.remind_at) }}</p>
                                </div>
                                <div class="flex shrink-0 gap-1">
                                    <button type="button" class="rounded bg-white px-2 py-1 text-[11px] font-semibold shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700" @click="completeFollowUp(item.id)">Done</button>
                                    <button type="button" class="rounded bg-white px-2 py-1 text-[11px] font-semibold shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700" @click="snoozeFollowUp(item.id)">Snooze</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="upcoming.length" class="mb-4">
                    <h4 class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-400">Upcoming</h4>
                    <div class="space-y-2">
                        <div v-for="item in upcoming" :key="'upcoming-'+item.id" class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold dark:text-white">@{{ item.title }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">@{{ item.assigned_user ? item.assigned_user.name : 'Unassigned' }}</p>
                                    <p class="mt-0.5 text-xs text-gray-400">@{{ formatDate(item.remind_at) }}</p>
                                </div>
                                <div class="flex shrink-0 gap-1">
                                    <button type="button" class="rounded bg-white px-2 py-1 text-[11px] font-semibold shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700" @click="completeFollowUp(item.id)">Done</button>
                                    <button type="button" class="rounded bg-white px-2 py-1 text-[11px] font-semibold shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700" @click="snoozeFollowUp(item.id)">Snooze</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!overdue.length && !today.length && !upcoming.length && !completed.length" class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700">
                    No follow-ups yet.
                </div>

                <div v-if="completed.length" class="mt-2">
                    <details class="group">
                        <summary class="cursor-pointer text-xs font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            Recently completed (@{{ completed.length }})
                        </summary>
                        <div class="mt-2 space-y-2">
                            <div v-for="item in completed" :key="'completed-'+item.id" class="rounded-lg border border-gray-200 p-3 opacity-70 dark:border-gray-800">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-semibold dark:text-white">@{{ item.title }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500">@{{ item.assigned_user ? item.assigned_user.name : 'Unassigned' }}</p>
                                    </div>
                                    <span class="text-[11px] text-gray-400">Done @{{ formatDate(item.completed_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>
            </template>
        </div>
    </script>

    <script type="module">
        app.component('v-scalvion-upcoming-followups', {
            template: '#v-scalvion-upcoming-followups-template',

            data() {
                return {
                    isLoading: false,
                    overdue: [],
                    today: [],
                    upcoming: [],
                    completed: [],
                };
            },

            mounted() {
                this.fetchItems();
            },

            methods: {
                fetchItems() {
                    this.isLoading = true;

                    this.$axios.get('{{ route('admin.scalvion.follow-ups.overview') }}')
                        .then((response) => {
                            this.overdue = response.data.overdue || [];
                            this.today = response.data.today || [];
                            this.upcoming = response.data.upcoming || [];
                            this.completed = response.data.completed || [];
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                },

                completeFollowUp(id) {
                    this.$axios.post('{{ route('admin.scalvion.follow-ups.complete', '__id__') }}'.replace('__id__', id))
                        .then(() => this.fetchItems());
                },

                snoozeFollowUp(id) {
                    const nextDay = new Date(Date.now() + 86400000).toISOString();

                    this.$axios.post('{{ route('admin.scalvion.follow-ups.snooze', '__id__') }}'.replace('__id__', id), {
                        snoozed_until: nextDay,
                    }).then(() => this.fetchItems());
                },

                formatDate(value) {
                    return value ? new Date(value).toLocaleString() : 'No reminder date';
                },
            },
        });
    </script>
@endPushOnce
