@php
    $users = app(\Webkul\User\Repositories\UserRepository::class)->all(['id', 'name']);
@endphp

<v-scalvion-entity-panels
    entity-type="{{ $entityType }}"
    entity-id="{{ $entityId }}"
    entity-label="{{ $entityLabel }}"
    :users='@json($users->values())'
>
    <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="light-shimmer-bg dark:shimmer h-5 w-32 rounded"></div>
    </div>
</v-scalvion-entity-panels>

@pushOnce('scripts')
    <script type="text/x-template" id="v-scalvion-entity-panels-template">
        <div class="mt-4 grid gap-4 xl:grid-cols-3">
            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-base font-semibold dark:text-white">Follow-ups</h3>
                    <p class="text-xs text-gray-500">Create reminders and keep ownership clear.</p>
                </div>

                <div class="grid gap-3">
                    <input v-model="form.title" type="text" class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white" placeholder="Follow-up title">
                    <textarea v-model="form.note" rows="3" class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white" placeholder="Internal note"></textarea>
                    <input v-model="form.remind_at" type="datetime-local" class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                    <select v-model="form.assigned_user_id" class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        <option value="">Assign user</option>
                        <option v-for="user in users" :key="user.id" :value="String(user.id)">@{{ user.name }}</option>
                    </select>
                    <button type="button" class="primary-button justify-center" @click="createFollowUp">Create Follow-up</button>
                </div>

                <div class="mt-4 space-y-3">
                    <div v-for="item in followUps" :key="item.id" class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-semibold dark:text-white">@{{ item.title }}</p>
                                <p v-if="item.note" class="mt-1 text-sm text-gray-500">@{{ item.note }}</p>
                            </div>
                            <span class="rounded-full bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">@{{ item.status }}</span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-400">
                            <span>@{{ formatDate(item.remind_at) }}</span>
                            <span v-if="item.assigned_user">| @{{ item.assigned_user.name }}</span>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <button type="button" class="secondary-button" @click="completeFollowUp(item.id)">Complete</button>
                            <button type="button" class="secondary-button" @click="snoozeFollowUp(item.id)">Snooze 1 day</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-base font-semibold dark:text-white">Timeline</h3>
                    <p class="text-xs text-gray-500">One shared activity stream for this record.</p>
                </div>

                <div class="space-y-3">
                    <div v-for="item in timeline" :key="`${item.source}-${item.created_at}-${item.title}`" class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <p class="font-semibold dark:text-white">@{{ item.title }}</p>
                        <p v-if="item.description" class="mt-1 text-sm text-gray-500">@{{ item.description }}</p>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-400">
                            <span>@{{ item.source }}</span>
                            <span>|</span>
                            <span>@{{ item.user || 'System' }}</span>
                            <span>|</span>
                            <span>@{{ formatDate(item.created_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-base font-semibold dark:text-white">Audit Log</h3>
                    <p class="text-xs text-gray-500">Structured before and after changes.</p>
                </div>

                <div class="space-y-3">
                    <div v-for="item in audits" :key="`${item.action}-${item.created_at}`" class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold capitalize dark:text-white">@{{ item.action }}</p>
                            <span class="text-xs text-gray-400">@{{ formatDate(item.created_at) }}</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">@{{ item.user || 'System' }}</p>
                        <pre class="mt-2 overflow-x-auto rounded bg-gray-50 p-2 text-[11px] text-gray-600 dark:bg-gray-950 dark:text-gray-300">@{{ formatAudit(item) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-scalvion-entity-panels', {
            template: '#v-scalvion-entity-panels-template',

            props: ['entityType', 'entityId', 'entityLabel', 'users'],

            data() {
                return {
                    followUps: [],
                    timeline: [],
                    audits: [],
                    form: {
                        title: '',
                        note: '',
                        remind_at: '',
                        assigned_user_id: '',
                    },
                };
            },

            mounted() {
                this.reload();
            },

            methods: {
                reload() {
                    this.$axios.get('{{ route('admin.scalvion.follow-ups.index', ['entityType' => '__type__', 'entityId' => '__id__']) }}'.replace('__type__', this.entityType).replace('__id__', this.entityId))
                        .then((response) => this.followUps = response.data.data || []);

                    this.$axios.get('{{ route('admin.scalvion.timeline.index', ['entityType' => '__type__', 'entityId' => '__id__']) }}'.replace('__type__', this.entityType).replace('__id__', this.entityId))
                        .then((response) => this.timeline = response.data.data || []);

                    this.$axios.get('{{ route('admin.scalvion.audit-logs.entity', ['entityType' => '__type__', 'entityId' => '__id__']) }}'.replace('__type__', this.entityType).replace('__id__', this.entityId))
                        .then((response) => this.audits = response.data.data || []);
                },

                createFollowUp() {
                    this.$axios.post('{{ route('admin.scalvion.follow-ups.store') }}', {
                        entity_type: this.entityType,
                        entity_id: this.entityId,
                        title: this.form.title,
                        note: this.form.note,
                        remind_at: this.form.remind_at || null,
                        assigned_user_id: this.form.assigned_user_id || null,
                    }).then(() => {
                        this.form = {
                            title: '',
                            note: '',
                            remind_at: '',
                            assigned_user_id: '',
                        };

                        this.reload();
                    });
                },

                completeFollowUp(id) {
                    this.$axios.post('{{ route('admin.scalvion.follow-ups.complete', '__id__') }}'.replace('__id__', id))
                        .then(() => this.reload());
                },

                snoozeFollowUp(id) {
                    const nextDay = new Date(Date.now() + 86400000).toISOString();

                    this.$axios.post('{{ route('admin.scalvion.follow-ups.snooze', '__id__') }}'.replace('__id__', id), {
                        snoozed_until: nextDay,
                    }).then(() => this.reload());
                },

                formatDate(value) {
                    return value ? new Date(value).toLocaleString() : 'No date';
                },

                formatAudit(item) {
                    return JSON.stringify({
                        before: item.before,
                        after: item.after,
                    }, null, 2);
                },
            },
        });
    </script>
@endPushOnce
