<x-admin::layouts>
    <x-slot:title>
        {{ __('whatsapp::app.inbox.title') }}
    </x-slot:title>

    <div class="page-content">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ __('whatsapp::app.inbox.title') }}</h1>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <x-admin::datagrid :src="route('admin.whatsapp.inbox.index')">
                <template #header="{ isLoading, available, applied, selectAll, sort, performAction }">
                    <div class="row grid grid-cols-[4fr_1fr_1fr_2fr] grid-rows-1 items-center gap-x-4 border-b px-4 py-3 text-sm font-medium text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        <div class="flex items-center gap-2">
                            <label for="mass_action_select_all_records">
                                <input
                                    type="checkbox"
                                    name="mass_action_select_all_records"
                                    id="mass_action_select_all_records"
                                    class="peer hidden"
                                    :checked="['all', 'partial'].includes(applied.massActions.meta.mode)"
                                    @change="selectAll"
                                >

                                <span
                                    class="icon-checkbox-outline cursor-pointer rounded-md text-2xl text-gray-600 dark:text-gray-300"
                                    :class="[
                                        applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checkbox-select peer-checked:text-brandColor' : (
                                            applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-multiple peer-checked:text-brandColor' : ''
                                        ),
                                    ]"
                                ></span>
                            </label>

                            {{ __('whatsapp::app.inbox.datagrid.contact') }}
                        </div>

                        <div>{{ __('whatsapp::app.inbox.datagrid.unread') }}</div>
                        <div>{{ __('whatsapp::app.inbox.datagrid.status') }}</div>
                        <div class="text-right">{{ __('whatsapp::app.inbox.datagrid.last_message') }}</div>
                    </div>
                </template>

                <template #body="{ isLoading, available }">
                    <tbody>
                        <template v-if="isLoading">
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Loading...
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="! available.records.length">
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('whatsapp::app.inbox.datagrid.empty') }}
                                </td>
                            </tr>
                        </template>

                        <template v-else>
                            <tr
                                v-for="record in available.records"
                                :key="record.id"
                                class="border-b last:border-b-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900"
                            >
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    @{{ record.contact }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    @{{ record.unread_count }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    @{{ record.status }}
                                </td>
                                <td class="px-4 py-4 text-right text-sm text-gray-700 dark:text-gray-300">
                                    @{{ record.last_message_at }}
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </template>
            </x-admin::datagrid>
        </div>
    </div>
</x-admin::layouts>
