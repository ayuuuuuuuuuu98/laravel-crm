<div class="wa-page">
    <div class="wa-page-heading">
        <div>
            <h1 class="text-xl font-semibold dark:text-white">{{ __('whatsapp::app.inbox.title') }}</h1>
            <p class="text-sm text-gray-500">{{ __('whatsapp::app.inbox.subtitle') }}</p>
        </div>

        <a
            href="{{ route('admin.whatsapp.settings.index') }}"
            class="secondary-button"
        >
            {{ __('whatsapp::app.inbox.configure') }}
        </a>
    </div>

    <v-whatsapp-inbox
        :initial-selected-id='@json($selectedConversationId)'
        :users='@json($users->values())'
        :tags='@json($tags->values())'
        list-endpoint="{{ route('admin.whatsapp.inbox.list') }}"
        detail-endpoint-template="{{ route('admin.whatsapp.inbox.show', '__id__') }}"
        save-endpoint-template="{{ route('admin.whatsapp.inbox.save', '__id__') }}"
        message-store-endpoint-template="{{ route('admin.whatsapp.inbox.messages.store', '__id__') }}"
        note-endpoint-template="{{ route('admin.whatsapp.inbox.notes.store_ajax', '__id__') }}"
        contact-create-endpoint-template="{{ route('admin.whatsapp.inbox.contact.create', '__id__') }}"
        lead-create-endpoint-template="{{ route('admin.whatsapp.inbox.lead.create_ajax', '__id__') }}"
        read-endpoint-template="{{ route('admin.whatsapp.inbox.read', '__id__') }}"
        unread-endpoint-template="{{ route('admin.whatsapp.inbox.unread', '__id__') }}"
        archive-endpoint-template="{{ route('admin.whatsapp.inbox.archive', '__id__') }}"
        unarchive-endpoint-template="{{ route('admin.whatsapp.inbox.unarchive', '__id__') }}"
        pin-endpoint-template="{{ route('admin.whatsapp.inbox.pin', '__id__') }}"
        unpin-endpoint-template="{{ route('admin.whatsapp.inbox.unpin', '__id__') }}"
        tag-attach-endpoint-template="{{ route('admin.whatsapp.inbox.tags.attach', '__id__') }}"
        tag-detach-endpoint-template="{{ route('admin.whatsapp.inbox.tags.detach', '__id__') }}"
        close-endpoint-template="{{ route('admin.whatsapp.inbox.close', '__id__') }}"
        reopen-endpoint-template="{{ route('admin.whatsapp.inbox.reopen', '__id__') }}"
        delete-endpoint-template="{{ route('admin.whatsapp.inbox.delete', '__id__') }}"
        restore-endpoint-template="{{ route('admin.whatsapp.inbox.restore', '__id__') }}"
        activity-endpoint-template="{{ route('admin.whatsapp.inbox.activities.index', '__id__') }}"
    />

    @pushOnce('styles')
        <style>
            .wa-page {
                display: flex;
                min-height: calc(100dvh - 8rem);
                flex-direction: column;
                gap: 1rem;
            }

            .wa-page-heading {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 1.75rem;
                padding: 1rem 1.25rem;
                background:
                    linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.92) 100%);
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.05);
            }

            .dark .wa-page-heading {
                border-color: rgba(31, 41, 55, 0.95);
                background:
                    linear-gradient(135deg, rgba(17, 24, 39, 0.96) 0%, rgba(15, 23, 42, 0.94) 100%);
                box-shadow: 0 18px 45px rgba(2, 6, 23, 0.28);
            }

            .wa-inbox-shell {
                display: flex;
                min-height: 0;
                flex-direction: column;
                gap: 1rem;
                flex: 1 1 auto;
            }

            .wa-inbox-grid {
                display: grid;
                min-height: 0;
                flex: 1 1 auto;
                gap: 1rem;
                align-items: stretch;
            }

            .wa-pane {
                min-height: 0;
                height: 100%;
            }

            .wa-sidebar-pane {
                grid-column: 1 / -1;
            }

            .wa-pane {
                position: relative;
            }

            .wa-scroll {
                scrollbar-width: thin;
                scrollbar-color: rgba(148, 163, 184, 0.65) transparent;
            }

            .wa-scroll::-webkit-scrollbar {
                width: 10px;
            }

            .wa-scroll::-webkit-scrollbar-track {
                background: transparent;
            }

            .wa-scroll::-webkit-scrollbar-thumb {
                border: 2px solid transparent;
                border-radius: 999px;
                background-clip: padding-box;
                background-color: rgba(148, 163, 184, 0.55);
            }

            .wa-pane-header {
                background:
                    linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(255, 255, 255, 0.98) 100%);
                backdrop-filter: blur(10px);
            }

            .dark .wa-pane-header {
                background:
                    linear-gradient(180deg, rgba(15, 23, 42, 0.94) 0%, rgba(17, 24, 39, 0.98) 100%);
            }

            .wa-toolbar {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .wa-toolbar .secondary-button,
            .wa-toolbar .primary-button,
            .wa-inbox-shell .secondary-button,
            .wa-inbox-shell .primary-button {
                min-height: 2.5rem;
                border-radius: 1rem;
                font-size: 0.875rem;
                font-weight: 600;
                line-height: 1.1;
            }

            .wa-conversation-card {
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            }

            .wa-thread-surface {
                background:
                    radial-gradient(circle at top left, rgba(14, 165, 233, 0.08), transparent 34%),
                    radial-gradient(circle at bottom right, rgba(16, 185, 129, 0.07), transparent 32%);
            }

            .dark .wa-thread-surface {
                background:
                    radial-gradient(circle at top left, rgba(14, 165, 233, 0.1), transparent 34%),
                    radial-gradient(circle at bottom right, rgba(16, 185, 129, 0.08), transparent 32%);
            }

            .wa-message-bubble {
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            }

            .wa-composer {
                box-shadow: 0 -10px 28px rgba(15, 23, 42, 0.05);
            }

            .wa-sidebar-stack > section,
            .wa-contact-grid > div {
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            }

            @media (min-width: 768px) {
                .wa-page {
                    min-height: calc(100dvh - 9rem);
                }

                .wa-inbox-shell {
                    overflow: hidden;
                }

                .wa-inbox-grid {
                    grid-template-columns: minmax(280px, 22rem) minmax(0, 1fr);
                }
            }

            @media (min-width: 1024px) {
                .wa-page {
                    min-height: calc(100dvh - 8.75rem);
                }

                .wa-inbox-grid {
                    grid-template-columns: minmax(300px, 25%) minmax(0, 1fr) minmax(300px, 25%);
                }

                .wa-sidebar-pane {
                    grid-column: auto;
                }
            }
        </style>
    @endPushOnce

    @pushOnce('scripts')
        <script type="text/x-template" id="v-whatsapp-inbox-template">
            <div class="wa-inbox-shell">
                <div class="wa-inbox-grid">
                    <aside class="wa-pane min-h-0 overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex h-full min-h-0 flex-col">
                            <div class="wa-pane-header sticky top-0 z-[4] border-b border-gray-200 px-4 py-4 dark:border-gray-800">
                                <div class="mb-4 flex items-start justify-between gap-3">
                                    <div>
                                        <h2 class="text-base font-semibold dark:text-white">@lang('whatsapp::app.inbox.list_title')</h2>
                                        <p class="text-xs text-gray-500">@{{ pagination.total || conversations.length }} active conversations</p>
                                    </div>

                                    <button type="button" class="secondary-button" @click="resetFilters">
                                        @lang('whatsapp::app.inbox.filters.clear')
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <input
                                        v-model="filters.search"
                                        type="search"
                                        class="w-full rounded-2xl border border-gray-300 bg-white px-3 py-3 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                                        placeholder="@lang('whatsapp::app.inbox.search_placeholder')"
                                    >

                                    <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                                        <label class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 px-3 py-2 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">
                                            <input v-model="filters.unread" type="checkbox" class="rounded border-gray-300 text-brandColor focus:ring-brandColor">
                                            <span>@lang('whatsapp::app.inbox.filters.unread')</span>
                                        </label>

                                        <label class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 px-3 py-2 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">
                                            <input v-model="filters.include_archived" type="checkbox" class="rounded border-gray-300 text-brandColor focus:ring-brandColor">
                                            <span>@lang('whatsapp::app.inbox.filters.include_archived')</span>
                                        </label>
                                    </div>

                                    <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                                        <select v-model="filters.assigned_user_id" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                            <option value="">@lang('whatsapp::app.inbox.filters.assigned')</option>
                                            <option v-for="user in users" :key="'filter-user-'+user.id" :value="String(user.id)">@{{ user.name }}</option>
                                        </select>

                                        <select v-model="filters.tag_id" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                            <option value="">@lang('whatsapp::app.inbox.filters.label')</option>
                                            <option v-for="tag in tags" :key="'filter-tag-'+tag.id" :value="String(tag.id)">@{{ tag.name }}</option>
                                        </select>
                                    </div>

                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <select v-model="filters.status" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                            <option value="">@lang('whatsapp::app.inbox.filters.status')</option>
                                            <option value="open">@lang('whatsapp::app.inbox.statuses.open')</option>
                                            <option value="pending">@lang('whatsapp::app.inbox.statuses.pending')</option>
                                            <option value="closed">@lang('whatsapp::app.inbox.statuses.closed')</option>
                                        </select>

                                        <select v-model="filters.priority" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                            <option value="">@lang('whatsapp::app.inbox.filters.priority')</option>
                                            <option value="low">@lang('whatsapp::app.inbox.priorities.low')</option>
                                            <option value="medium">@lang('whatsapp::app.inbox.priorities.medium')</option>
                                            <option value="high">@lang('whatsapp::app.inbox.priorities.high')</option>
                                            <option value="urgent">@lang('whatsapp::app.inbox.priorities.urgent')</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="wa-scroll min-h-0 flex-1 overflow-y-auto" ref="listScroller">
                                <div v-if="isLoadingList && ! conversations.length" class="space-y-3 p-4">
                                    <div v-for="item in 6" :key="'list-skeleton-'+item" class="animate-pulse rounded-3xl border border-gray-200 p-4 dark:border-gray-800">
                                        <div class="mb-4 flex items-center gap-3">
                                            <div class="h-11 w-11 rounded-2xl bg-gray-200 dark:bg-gray-800"></div>
                                            <div class="flex-1 space-y-2">
                                                <div class="h-4 w-1/2 rounded bg-gray-200 dark:bg-gray-800"></div>
                                                <div class="h-3 w-1/3 rounded bg-gray-200 dark:bg-gray-800"></div>
                                            </div>
                                        </div>
                                        <div class="mb-2 h-3 w-full rounded bg-gray-200 dark:bg-gray-800"></div>
                                        <div class="h-3 w-2/3 rounded bg-gray-200 dark:bg-gray-800"></div>
                                    </div>
                                </div>

                                <div v-else-if="listError" class="p-4">
                                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/40 dark:text-red-200">
                                        @lang('whatsapp::app.inbox.errors.load_list')
                                    </div>
                                </div>

                                <div v-else-if="! conversations.length" class="grid gap-3 p-8 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                                        WA
                                    </div>
                                    <div>
                                        <p class="font-semibold dark:text-white">@lang('whatsapp::app.inbox.empty_state')</p>
                                        <p class="mt-1 text-sm text-gray-500">Try a broader search, clear a filter, or wait for new inbound messages.</p>
                                    </div>
                                </div>

                                <div v-else class="space-y-3 p-4">
                                    <button
                                        v-for="conversation in conversations"
                                        :key="conversation.id"
                                        type="button"
                                        class="wa-conversation-card w-full rounded-[24px] border p-4 text-left transition"
                                        :class="selectedConversation && selectedConversation.id === conversation.id
                                            ? 'border-brandColor/40 bg-brandColor/5 shadow-sm ring-1 ring-brandColor/20'
                                            : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700 dark:hover:bg-gray-950'"
                                        @click="openConversation(conversation.id)"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200">
                                                @{{ conversation.avatar_initials }}
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span class="truncate font-semibold text-gray-900 dark:text-white">@{{ conversation.contact_label }}</span>
                                                            <span v-if="conversation.is_pinned" class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">Pinned</span>
                                                            <span v-if="conversation.is_unread" class="rounded-full bg-brandColor px-2 py-0.5 text-[11px] font-medium text-white">@{{ conversation.unread_count }}</span>
                                                        </div>
                                                        <p class="truncate text-xs text-gray-500">@{{ conversation.contact_identifier }}</p>
                                                    </div>

                                                    <div class="shrink-0 text-right">
                                                        <p class="text-[11px] text-gray-500">@{{ conversation.last_message_time || '' }}</p>
                                                    </div>
                                                </div>

                                                <p class="mt-3 line-clamp-2 text-sm text-gray-600 dark:text-gray-300">@{{ conversation.last_message_preview }}</p>

                                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                                    <div class="flex flex-wrap items-center gap-2 text-[11px]">
                                                        <span class="rounded-full px-2.5 py-1 font-medium" :class="statusBadgeClass(conversation.status_tone)">
                                                            @{{ conversation.status_label }}
                                                        </span>
                                                        <span class="rounded-full px-2.5 py-1 font-medium" :class="priorityBadgeClass(conversation.priority_tone)">
                                                            @{{ conversation.priority_label }}
                                                        </span>
                                                    </div>

                                                    <div v-if="conversation.assigned_user" class="inline-flex min-w-0 items-center gap-2 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                                        <template v-if="conversation.assigned_user.image_url">
                                                            <img :src="conversation.assigned_user.image_url" :alt="conversation.assigned_user.name" class="h-5 w-5 rounded-full object-cover">
                                                        </template>
                                                        <template v-else>
                                                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/80 text-[9px] font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-100">
                                                                @{{ conversation.assigned_user.initials }}
                                                            </span>
                                                        </template>

                                                        <span class="truncate">@{{ conversation.assigned_user.name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </button>

                                    <div ref="infiniteSentinel" class="py-2 text-center text-xs text-gray-500">
                                        <span v-if="isLoadingMore">@lang('whatsapp::app.inbox.loading')</span>
                                        <span v-else-if="pagination.has_more">@lang('whatsapp::app.inbox.load_more')</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <main class="wa-pane min-h-0 overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div v-if="detailError" class="m-4 rounded-3xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/40 dark:text-red-200">
                            @lang('whatsapp::app.inbox.errors.load_detail')
                        </div>

                        <div v-else-if="! selectedConversation && ! isLoadingDetail" class="grid h-full min-h-[60vh] place-items-center p-8 text-center">
                            <div class="max-w-md">
                                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-300">WA</div>
                                <p class="text-lg font-semibold dark:text-white">@lang('whatsapp::app.inbox.select_prompt')</p>
                                <p class="mt-2 text-sm text-gray-500">Pick a conversation to unlock the thread, CRM details, notes, and follow-up actions.</p>
                            </div>
                        </div>

                        <div v-else class="flex h-full min-h-[60vh] min-w-0 flex-col">
                            <div v-if="isLoadingDetail && ! selectedConversation" class="animate-pulse space-y-4 p-6">
                                <div class="h-6 w-56 rounded bg-gray-200 dark:bg-gray-800"></div>
                                <div class="h-4 w-40 rounded bg-gray-200 dark:bg-gray-800"></div>
                                <div class="h-28 rounded bg-gray-200 dark:bg-gray-800"></div>
                            </div>

                            <template v-else-if="selectedConversation">
                                <div class="wa-pane-header sticky top-0 z-[5] border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-5">
                                    <div class="flex flex-wrap items-start justify-between gap-4">
                                        <div class="flex min-w-0 items-start gap-3">
                                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-base font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200">
                                                @{{ selectedConversation.avatar_initials }}
                                            </div>

                                            <div class="min-w-0">
                                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                                    <h2 class="truncate text-xl font-semibold dark:text-white">@{{ selectedConversation.contact_label }}</h2>
                                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="statusBadgeClass(selectedConversation.status_tone)">
                                                        @{{ selectedConversation.status_label }}
                                                    </span>
                                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="priorityBadgeClass(selectedConversation.priority_tone)">
                                                        @{{ selectedConversation.priority_label }}
                                                    </span>
                                                    <span v-if="selectedConversation.is_unread" class="rounded-full bg-brandColor/10 px-2.5 py-1 text-xs font-medium text-brandColor">
                                                        @{{ selectedConversation.unread_count }} unread
                                                    </span>
                                                </div>

                                                <p class="text-sm text-gray-500">@{{ selectedConversation.contact_identifier }}</p>

                                                <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                                    <span v-if="selectedConversation.assigned_user" class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-2.5 py-1 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                                        <span class="font-medium">Assigned</span>
                                                        <span>@{{ selectedConversation.assigned_user.name }}</span>
                                                    </span>

                                                    <span v-if="selectedConversation.follow_up_label" class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-2.5 py-1 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                                        <span class="font-medium">Follow-up</span>
                                                        <span>@{{ selectedConversation.follow_up_label }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="wa-toolbar">
                                            <button v-if="selectedConversation.is_unread" type="button" class="secondary-button" @click="toggleRead">
                                                @lang('whatsapp::app.inbox.actions.mark_read')
                                            </button>

                                            <button v-else type="button" class="secondary-button" @click="toggleRead">
                                                @lang('whatsapp::app.inbox.actions.mark_unread')
                                            </button>

                                            <button v-if="selectedConversation.is_pinned" type="button" class="secondary-button" @click="togglePin">
                                                @lang('whatsapp::app.inbox.actions.unpin')
                                            </button>

                                            <button v-else type="button" class="secondary-button" @click="togglePin">
                                                @lang('whatsapp::app.inbox.actions.pin')
                                            </button>

                                            <button v-if="selectedConversation.is_archived" type="button" class="secondary-button" @click="toggleArchive">
                                                @lang('whatsapp::app.inbox.actions.unarchive')
                                            </button>

                                            <button v-else type="button" class="secondary-button" @click="toggleArchive">
                                                @lang('whatsapp::app.inbox.actions.archive')
                                            </button>

                                            <button v-if="selectedConversation.status === 'closed'" type="button" class="secondary-button" @click="toggleStatus">
                                                @lang('whatsapp::app.inbox.actions.reopen')
                                            </button>

                                            <button v-else type="button" class="secondary-button" @click="toggleStatus">
                                                @lang('whatsapp::app.inbox.actions.close')
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="wa-thread-surface flex min-h-0 flex-1 flex-col bg-gray-50/60 dark:bg-gray-950/30">
                                    <div class="wa-pane-header sticky top-0 z-[4] flex items-center justify-between border-b border-gray-200 px-5 py-3 dark:border-gray-800">
                                        <div>
                                            <h3 class="font-semibold dark:text-white">@lang('whatsapp::app.inbox.thread_title')</h3>
                                            <p class="text-xs text-gray-500">Incoming and outgoing messages stay inside the CRM view for context.</p>
                                        </div>

                                        <button
                                            v-if="messageMeta.has_more"
                                            type="button"
                                            class="text-xs font-medium text-brandColor hover:underline"
                                            @click="loadOlderMessages"
                                        >
                                            @lang('whatsapp::app.inbox.detail.load_older')
                                        </button>
                                    </div>

                                    <div ref="threadScroller" class="wa-scroll min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-5">
                                        <div v-if="isLoadingMessages && ! messages.length" class="space-y-3">
                                            <div v-for="item in 5" :key="'message-skeleton-'+item" class="animate-pulse rounded-3xl bg-white p-4 dark:bg-gray-900">
                                                <div class="mb-3 h-3 w-1/4 rounded bg-gray-200 dark:bg-gray-800"></div>
                                                <div class="h-12 rounded bg-gray-200 dark:bg-gray-800"></div>
                                            </div>
                                        </div>

                                        <div v-else-if="! groupedMessages.length" class="grid h-full min-h-[18rem] place-items-center rounded-3xl border border-dashed border-gray-300 bg-white px-4 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                            @lang('whatsapp::app.inbox.detail.no_messages')
                                        </div>

                                        <div v-else class="space-y-6">
                                            <div v-for="group in groupedMessages" :key="group.label" class="space-y-4">
                                                <div class="sticky top-0 z-[1] flex justify-center">
                                                    <span class="rounded-full border border-gray-200 bg-white/95 px-3 py-1 text-[11px] font-medium text-gray-500 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/95 dark:text-gray-300">
                                                        @{{ group.label }}
                                                    </span>
                                                </div>

                                                <div class="space-y-3">
                                                    <div
                                                        v-for="message in group.items"
                                                        :key="message.id"
                                                        class="flex"
                                                        :class="message.direction === 'outbound' ? 'justify-end' : 'justify-start'"
                                                    >
                                                        <div
                                                            class="wa-message-bubble max-w-[88%] rounded-[24px] px-4 py-3 shadow-sm sm:max-w-[80%]"
                                                            :class="message.direction === 'outbound'
                                                                ? 'bg-brandColor text-white'
                                                                : 'border border-gray-200 bg-white text-gray-800 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100'"
                                                        >
                                                            <div class="mb-2 flex flex-wrap items-center gap-2 text-xs" :class="message.direction === 'outbound' ? 'text-white/80' : 'text-gray-500 dark:text-gray-300'">
                                                                <span class="font-semibold">@{{ message.direction_label }}</span>
                                                                <span v-if="message.sender_name">@{{ message.sender_name }}</span>
                                                                <span>@{{ message.created_at_time }}</span>
                                                                <span v-if="message.status" class="rounded-full border px-2 py-0.5" :class="message.direction === 'outbound' ? 'border-white/30' : 'border-gray-200 dark:border-gray-700'">
                                                                    @{{ message.status_label }}
                                                                </span>
                                                            </div>

                                                            <p class="whitespace-pre-wrap text-sm leading-6">@{{ message.content || '@lang('whatsapp::app.inbox.detail.empty_message')' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wa-pane-header sticky bottom-0 z-[6] border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900 sm:px-5">
                                        <div class="wa-composer rounded-[24px] border border-gray-200 bg-gray-50 p-3 shadow-sm dark:border-gray-700 dark:bg-gray-950/40">
                                            <div class="mb-2 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                                <span class="rounded-full bg-emerald-100 px-2 py-1 font-medium text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200">Local send</span>
                                                <span>Messages are saved inside the CRM now. API delivery can be connected later without changing the workspace flow.</span>
                                            </div>

                                            <textarea
                                                ref="composerTextarea"
                                                v-model="composerDraft"
                                                rows="2"
                                                class="w-full rounded-2xl border border-gray-300 bg-white px-3 py-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                                placeholder="@lang('whatsapp::app.inbox.detail.composer_placeholder')"
                                                @keydown.ctrl.enter.prevent="sendMessage"
                                            ></textarea>

                                            <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                                                <div class="wa-toolbar">
                                                    <button type="button" class="secondary-button" @click="showPlaceholder('attachments')">Attachment</button>
                                                    <button type="button" class="secondary-button" @click="showPlaceholder('emoji')">Emoji</button>
                                                    <button type="button" class="secondary-button" @click="showPlaceholder('templates')">Template</button>
                                                </div>

                                                <div class="flex items-center gap-3">
                                                    <span class="text-xs text-gray-500">Press `Ctrl+Enter` to send</span>
                                                    <button type="button" class="primary-button" :disabled="! composerCanSend || isSendingMessage" @click="sendMessage">
                                                        <span v-if="isSendingMessage">Sending...</span>
                                                        <span v-else>Send Message</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </main>

                    <aside class="wa-pane wa-sidebar-pane min-h-0 overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div v-if="selectedConversation" class="flex h-full min-h-0 flex-col">
                            <div class="wa-pane-header sticky top-0 z-[4] flex items-center justify-between border-b border-gray-200 px-4 py-4 dark:border-gray-800">
                                <div>
                                    <h3 class="font-semibold dark:text-white">CRM Sidebar</h3>
                                    <p class="text-xs text-gray-500">Contact, notes, labels, follow-ups, and timeline in one fixed workspace.</p>
                                </div>

                                <button type="button" class="secondary-button" @click="saveConversation" :disabled="isSavingConversation">
                                    @lang('whatsapp::app.inbox.detail.save')
                                </button>
                            </div>

                            <div class="wa-scroll min-h-0 flex-1 overflow-y-auto p-4">
                                <div class="wa-sidebar-stack space-y-4">
                                    <section class="rounded-[24px] border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-950/30">
                                        <div class="mb-3 flex items-center justify-between gap-3">
                                            <div>
                                                <h4 class="font-semibold dark:text-white">Quick Actions</h4>
                                                <p class="text-xs text-gray-500">Jump to the CRM task you need most.</p>
                                            </div>
                                        </div>

                                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-2">
                                            <a v-if="selectedConversation.contact_url" :href="selectedConversation.contact_url" class="secondary-button justify-center">@lang('whatsapp::app.inbox.detail.open_contact')</a>
                                            <button v-else type="button" class="secondary-button justify-center" @click="createContact">@lang('whatsapp::app.inbox.detail.create_contact')</button>

                                            <a v-if="selectedConversation.lead_url" :href="selectedConversation.lead_url" class="secondary-button justify-center">@lang('whatsapp::app.inbox.detail.open_linked_lead')</a>
                                            <button v-else type="button" class="secondary-button justify-center" @click="createLead">@lang('whatsapp::app.inbox.detail.create_lead')</button>

                                            <button type="button" class="secondary-button justify-center" @click="toggleAssignmentFocus">Assign User</button>
                                            <button type="button" class="secondary-button justify-center" @click="focusNoteComposer">Add Internal Note</button>
                                            <button type="button" class="secondary-button justify-center" @click="focusTagPicker">Add Label</button>
                                            <button type="button" class="secondary-button justify-center" @click="focusFollowUpField">Set Follow-up</button>

                                            <button v-if="selectedConversation.status === 'closed'" type="button" class="secondary-button justify-center" @click="toggleStatus">@lang('whatsapp::app.inbox.actions.reopen')</button>
                                            <button v-else type="button" class="secondary-button justify-center" @click="toggleStatus">@lang('whatsapp::app.inbox.actions.close')</button>

                                            <button type="button" class="secondary-button justify-center" @click="deleteConversation">@lang('whatsapp::app.inbox.actions.delete')</button>
                                        </div>
                                    </section>

                                    <section class="wa-contact-grid grid gap-4 xl:grid-cols-2 2xl:grid-cols-1">
                                        <div class="rounded-[24px] border border-gray-200 p-4 dark:border-gray-800">
                                            <div class="mb-2 flex items-center justify-between gap-3">
                                                <h4 class="font-medium dark:text-white">Contact</h4>
                                                <a v-if="selectedConversation.contact_url" :href="selectedConversation.contact_url" class="text-xs font-medium text-brandColor hover:underline">Open</a>
                                            </div>

                                            <template v-if="selectedConversation.person">
                                                <p class="font-medium text-gray-900 dark:text-white">@{{ selectedConversation.person.name }}</p>
                                                <p v-if="selectedConversation.person.emails && selectedConversation.person.emails.length" class="mt-1 text-sm text-gray-500">@{{ selectedConversation.person.emails[0] }}</p>
                                                <p v-if="selectedConversation.person.contact_numbers && selectedConversation.person.contact_numbers.length" class="mt-1 text-sm text-gray-500">@{{ selectedConversation.person.contact_numbers[0] }}</p>
                                            </template>

                                            <p v-else class="text-sm text-gray-500">@lang('whatsapp::app.inbox.detail.no_contact')</p>
                                        </div>

                                        <div class="rounded-[24px] border border-gray-200 p-4 dark:border-gray-800">
                                            <div class="mb-2 flex items-center justify-between gap-3">
                                                <h4 class="font-medium dark:text-white">Lead</h4>
                                                <a v-if="selectedConversation.lead_url" :href="selectedConversation.lead_url" class="text-xs font-medium text-brandColor hover:underline">Open</a>
                                            </div>

                                            <template v-if="selectedConversation.lead">
                                                <p class="font-medium text-gray-900 dark:text-white">@{{ selectedConversation.lead.title }}</p>
                                                <p v-if="selectedConversation.lead.stage" class="mt-1 text-sm text-gray-500">@{{ selectedConversation.lead.stage }}</p>
                                            </template>

                                            <p v-else class="text-sm text-gray-500">@lang('whatsapp::app.inbox.detail.no_lead')</p>
                                        </div>

                                        <div class="rounded-[24px] border border-gray-200 p-4 dark:border-gray-800 xl:col-span-2 2xl:col-span-1">
                                            <div class="mb-2 flex items-center justify-between gap-3">
                                                <h4 class="font-medium dark:text-white">Company</h4>
                                                <a v-if="selectedConversation.organization && selectedConversation.organization.url" :href="selectedConversation.organization.url" class="text-xs font-medium text-brandColor hover:underline">Open</a>
                                            </div>

                                            <template v-if="selectedConversation.organization">
                                                <p class="font-medium text-gray-900 dark:text-white">@{{ selectedConversation.organization.name }}</p>
                                            </template>

                                            <p v-else class="text-sm text-gray-500">No linked company</p>
                                        </div>
                                    </section>

                                    <section class="rounded-[24px] border border-gray-200 p-4 dark:border-gray-800">
                                        <div class="mb-4">
                                            <h4 class="font-semibold dark:text-white">@lang('whatsapp::app.inbox.detail.crm_title')</h4>
                                            <p class="text-xs text-gray-500">Ownership, follow-up, and link management live here.</p>
                                        </div>

                                        <div class="grid gap-3">
                                            <select v-model="detailForm.status" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                                <option value="open">@lang('whatsapp::app.inbox.statuses.open')</option>
                                                <option value="pending">@lang('whatsapp::app.inbox.statuses.pending')</option>
                                                <option value="closed">@lang('whatsapp::app.inbox.statuses.closed')</option>
                                            </select>

                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <select ref="assignedUserField" v-model="detailForm.assigned_user_id" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                                    <option value="">@lang('whatsapp::app.inbox.detail.assigned_to')</option>
                                                    <option v-for="user in users" :key="'assigned-'+user.id" :value="String(user.id)">@{{ user.name }}</option>
                                                </select>

                                                <select v-model="detailForm.owner_user_id" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                                    <option value="">@lang('whatsapp::app.inbox.detail.owner')</option>
                                                    <option v-for="user in users" :key="'owner-'+user.id" :value="String(user.id)">@{{ user.name }}</option>
                                                </select>
                                            </div>

                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <select v-model="detailForm.priority" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                                    <option value="low">@lang('whatsapp::app.inbox.priorities.low')</option>
                                                    <option value="medium">@lang('whatsapp::app.inbox.priorities.medium')</option>
                                                    <option value="high">@lang('whatsapp::app.inbox.priorities.high')</option>
                                                    <option value="urgent">@lang('whatsapp::app.inbox.priorities.urgent')</option>
                                                </select>

                                                <input ref="followUpField" v-model="detailForm.follow_up_at" type="datetime-local" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                            </div>

                                            <input v-model="detailForm.next_action" type="text" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white" placeholder="@lang('whatsapp::app.inbox.detail.next_action')">

                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <select v-model="detailForm.person_id" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                                    <option value="">@lang('whatsapp::app.inbox.detail.no_contact')</option>
                                                    <option v-if="selectedConversation.person" :value="String(selectedConversation.person.id)">@{{ selectedConversation.person.name }}</option>
                                                    <option v-for="person in suggestedPersons" :key="'person-'+person.id" :value="String(person.id)">@{{ person.name }}</option>
                                                </select>

                                                <select v-model="detailForm.lead_id" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                                    <option value="">@lang('whatsapp::app.inbox.detail.no_lead')</option>
                                                    <option v-if="selectedConversation.lead" :value="String(selectedConversation.lead.id)">@{{ selectedConversation.lead.title }}</option>
                                                    <option v-for="lead in suggestedLeads" :key="'lead-'+lead.id" :value="String(lead.id)">@{{ lead.title }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </section>

                                    <section class="rounded-[24px] border border-gray-200 p-4 dark:border-gray-800">
                                        <div class="mb-4">
                                            <h4 class="font-semibold dark:text-white">@lang('whatsapp::app.inbox.detail.tags.title')</h4>
                                            <p class="text-xs text-gray-500">Reuse the existing Krayin tagging system for inbox segmentation.</p>
                                        </div>

                                        <div class="grid gap-3">
                                            <select ref="tagPicker" v-model="tagToAttach" class="rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                                <option value="">@lang('whatsapp::app.inbox.filters.label')</option>
                                                <option v-for="tag in availableAttachableTags" :key="'attach-'+tag.id" :value="String(tag.id)">@{{ tag.name }}</option>
                                            </select>

                                            <button type="button" class="secondary-button justify-center" :disabled="! tagToAttach" @click="attachTag">
                                                Add Label
                                            </button>

                                            <div v-if="selectedConversation.tags && selectedConversation.tags.length" class="flex flex-wrap gap-2">
                                                <button
                                                    v-for="tag in selectedConversation.tags"
                                                    :key="'selected-tag-'+tag.id"
                                                    type="button"
                                                    class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium text-gray-800 dark:text-white"
                                                    :style="{ backgroundColor: `${tag.color || '#e5e7eb'}22`, borderColor: tag.color || '#e5e7eb' }"
                                                    @click="detachTag(tag.id)"
                                                >
                                                    <span>@{{ tag.name }}</span>
                                                    <span class="text-[10px]">remove</span>
                                                </button>
                                            </div>

                                            <p v-else class="text-sm text-gray-500">No labels attached yet.</p>
                                        </div>
                                    </section>

                                    <section class="rounded-[24px] border border-gray-200 p-4 dark:border-gray-800">
                                        <div class="mb-4">
                                            <h4 class="font-semibold dark:text-white">@lang('whatsapp::app.inbox.detail.note_title')</h4>
                                            <p class="text-xs text-gray-500">Notes stay internal and are never sent to WhatsApp.</p>
                                        </div>

                                        <div class="space-y-3">
                                            <div v-if="notes.length" class="space-y-3">
                                                <div v-for="note in notes" :key="'note-'+note.id" class="rounded-2xl border border-gray-200 p-3 dark:border-gray-800">
                                                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-200">@{{ note.user ? note.user.name : 'System' }}</span>
                                                        <span class="text-xs text-gray-500">@{{ note.created_at_label }}</span>
                                                    </div>
                                                    <p class="whitespace-pre-wrap text-sm text-gray-600 dark:text-gray-300">@{{ note.comment }}</p>
                                                </div>
                                            </div>

                                            <p v-else class="text-sm text-gray-500">No internal notes yet.</p>

                                            <textarea
                                                ref="noteTextarea"
                                                v-model="newNote"
                                                rows="4"
                                                class="w-full rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                                                placeholder="@lang('whatsapp::app.inbox.detail.note_placeholder')"
                                            ></textarea>

                                            <button type="button" class="primary-button justify-center" :disabled="! newNote.trim()" @click="saveNote">
                                                @lang('whatsapp::app.inbox.detail.add_note')
                                            </button>
                                        </div>
                                    </section>

                                    <section class="rounded-[24px] border border-gray-200 p-4 dark:border-gray-800">
                                        <div class="mb-4 flex items-center justify-between">
                                            <div>
                                                <h4 class="font-semibold dark:text-white">@lang('whatsapp::app.inbox.detail.timeline_title')</h4>
                                                <p class="text-xs text-gray-500">Recent CRM events keep ownership and changes auditable.</p>
                                            </div>

                                            <button type="button" class="text-xs font-medium text-brandColor hover:underline" @click="loadActivities">
                                                Refresh
                                            </button>
                                        </div>

                                        <div v-if="isLoadingActivities" class="space-y-3">
                                            <div v-for="item in 4" :key="'activity-skeleton-'+item" class="animate-pulse rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                                                <div class="mb-2 h-4 w-1/3 rounded bg-gray-200 dark:bg-gray-800"></div>
                                                <div class="h-3 w-full rounded bg-gray-200 dark:bg-gray-800"></div>
                                            </div>
                                        </div>

                                        <div v-else-if="! activities.length" class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                            @lang('whatsapp::app.inbox.detail.activity_empty')
                                        </div>

                                        <div v-else class="space-y-3">
                                            <div v-for="activity in activities" :key="'activity-'+activity.id" class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                                                <div class="mb-2 flex flex-wrap items-center gap-2 text-sm">
                                                    <span class="font-semibold dark:text-white">@{{ activity.title || activity.type }}</span>
                                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">@{{ activity.type }}</span>
                                                </div>
                                                <p v-if="activity.comment" class="text-sm text-gray-600 dark:text-gray-300">@{{ activity.comment }}</p>
                                                <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                                                    <span>@{{ activity.user ? activity.user.name : 'System' }}</span>
                                                    <span>@{{ activity.created_at_label || formatActivityDate(activity.created_at) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>

                        <div v-else class="grid h-full min-h-[20rem] place-items-center p-8 text-center">
                            <div class="max-w-sm">
                                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-300">CRM</div>
                                <p class="font-semibold dark:text-white">CRM sidebar</p>
                                <p class="mt-2 text-sm text-gray-500">Select a conversation to pin contact context, notes, labels, follow-ups, and timeline here.</p>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-whatsapp-inbox', {
                template: '#v-whatsapp-inbox-template',

                props: {
                    initialSelectedId: {
                        type: Number,
                        default: null,
                    },

                    users: {
                        type: Array,
                        default: () => [],
                    },

                    tags: {
                        type: Array,
                        default: () => [],
                    },

                    listEndpoint: String,
                    detailEndpointTemplate: String,
                    saveEndpointTemplate: String,
                    messageStoreEndpointTemplate: String,
                    noteEndpointTemplate: String,
                    contactCreateEndpointTemplate: String,
                    leadCreateEndpointTemplate: String,
                    readEndpointTemplate: String,
                    unreadEndpointTemplate: String,
                    archiveEndpointTemplate: String,
                    unarchiveEndpointTemplate: String,
                    pinEndpointTemplate: String,
                    unpinEndpointTemplate: String,
                    tagAttachEndpointTemplate: String,
                    tagDetachEndpointTemplate: String,
                    closeEndpointTemplate: String,
                    reopenEndpointTemplate: String,
                    deleteEndpointTemplate: String,
                    restoreEndpointTemplate: String,
                    activityEndpointTemplate: String,
                },

                data() {
                    return {
                        filters: {
                            search: '',
                            unread: false,
                            include_archived: false,
                            assigned_user_id: '',
                            status: '',
                            tag_id: '',
                            priority: '',
                        },

                        conversations: [],
                        pagination: {
                            current_page: 0,
                            last_page: 0,
                            per_page: 20,
                            total: 0,
                            has_more: false,
                        },

                        selectedConversation: null,
                        messages: [],
                        activities: [],
                        notes: [],
                        messageMeta: {
                            has_more: false,
                            next_before_id: null,
                        },
                        suggestedPersons: [],
                        suggestedLeads: [],
                        detailForm: {
                            status: 'open',
                            assigned_user_id: '',
                            owner_user_id: '',
                            priority: 'medium',
                            follow_up_at: '',
                            next_action: '',
                            person_id: '',
                            lead_id: '',
                        },
                        newNote: '',
                        composerDraft: '',
                        tagToAttach: '',
                        searchDebounce: null,
                        observer: null,
                        isLoadingList: false,
                        isLoadingMore: false,
                        isLoadingDetail: false,
                        isLoadingMessages: false,
                        isLoadingActivities: false,
                        isSendingMessage: false,
                        isSavingConversation: false,
                        listError: false,
                        detailError: false,
                    };
                },

                computed: {
                    availableAttachableTags() {
                        const selectedTags = this.selectedConversation && this.selectedConversation.tags
                            ? this.selectedConversation.tags
                            : [];
                        const selectedIds = new Set(selectedTags.map((tag) => String(tag.id)));

                        return this.tags.filter((tag) => ! selectedIds.has(String(tag.id)));
                    },

                    groupedMessages() {
                        const groups = [];
                        let currentGroup = null;

                        for (const message of this.messages) {
                            if (! currentGroup || currentGroup.label !== message.date_label) {
                                currentGroup = {
                                    label: message.date_label || 'Unknown date',
                                    items: [],
                                };

                                groups.push(currentGroup);
                            }

                            currentGroup.items.push(message);
                        }

                        return groups;
                    },

                    composerCanSend() {
                        return !! (this.selectedConversation && this.composerDraft.trim().length);
                    },
                },

                watch: {
                    'filters.search'() {
                        clearTimeout(this.searchDebounce);

                        this.searchDebounce = setTimeout(() => {
                            this.fetchConversations(true);
                        }, 300);
                    },

                    'filters.unread'() {
                        this.fetchConversations(true);
                    },

                    'filters.include_archived'() {
                        this.fetchConversations(true);
                    },

                    'filters.assigned_user_id'() {
                        this.fetchConversations(true);
                    },

                    'filters.status'() {
                        this.fetchConversations(true);
                    },

                    'filters.tag_id'() {
                        this.fetchConversations(true);
                    },

                    'filters.priority'() {
                        this.fetchConversations(true);
                    },
                },

                mounted() {
                    this.fetchConversations(true);
                    this.setupInfiniteScroll();
                },

                beforeUnmount() {
                    clearTimeout(this.searchDebounce);

                    if (this.observer) {
                        this.observer.disconnect();
                    }
                },

                methods: {
                    endpoint(template, id) {
                        return template.replace('__id__', id);
                    },

                    statusBadgeClass(tone) {
                        if (tone === 'rose') {
                            return 'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-200';
                        }

                        if (tone === 'amber') {
                            return 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-200';
                        }

                        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200';
                    },

                    priorityBadgeClass(tone) {
                        if (tone === 'rose') {
                            return 'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-200';
                        }

                        if (tone === 'amber') {
                            return 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-200';
                        }

                        if (tone === 'slate') {
                            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200';
                        }

                        return 'bg-sky-100 text-sky-700 dark:bg-sky-950/50 dark:text-sky-200';
                    },

                    resetFilters() {
                        this.filters = {
                            search: '',
                            unread: false,
                            include_archived: false,
                            assigned_user_id: '',
                            status: '',
                            tag_id: '',
                            priority: '',
                        };
                    },

                    setupInfiniteScroll() {
                        this.$nextTick(() => {
                            if (! this.$refs.infiniteSentinel || ! window.IntersectionObserver) {
                                return;
                            }

                            this.observer = new IntersectionObserver((entries) => {
                                if (entries.some((entry) => entry.isIntersecting) && this.pagination.has_more && ! this.isLoadingMore) {
                                    this.fetchConversations(false);
                                }
                            }, {
                                root: this.$refs.listScroller,
                                threshold: 0.2,
                            });

                            this.observer.observe(this.$refs.infiniteSentinel);
                        });
                    },

                    async fetchConversations(reset = false) {
                        if (reset) {
                            this.isLoadingList = true;
                            this.pagination.current_page = 0;
                        } else {
                            this.isLoadingMore = true;
                        }

                        this.listError = false;

                        try {
                            const page = reset ? 1 : this.pagination.current_page + 1;
                            const response = await this.$axios.get(this.listEndpoint, {
                                params: {
                                    ...this.filters,
                                    unread: this.filters.unread ? 1 : '',
                                    include_archived: this.filters.include_archived ? 1 : '',
                                    page,
                                    per_page: 20,
                                },
                            });

                            const payload = response.data;

                            this.conversations = reset
                                ? payload.data
                                : [...this.conversations, ...payload.data];

                            this.pagination = payload.meta;

                            if (! this.selectedConversation && this.conversations.length) {
                                const preferredId = this.initialSelectedId || this.conversations[0].id;
                                await this.openConversation(preferredId);
                            } else if (this.selectedConversation) {
                                const updated = this.conversations.find((item) => item.id === this.selectedConversation.id);

                                if (updated) {
                                    this.selectedConversation = {
                                        ...this.selectedConversation,
                                        ...updated,
                                    };
                                } else if (reset) {
                                    if (this.conversations.length) {
                                        await this.openConversation(this.conversations[0].id);
                                    } else {
                                        this.selectedConversation = null;
                                        this.messages = [];
                                        this.activities = [];
                                        this.notes = [];
                                    }
                                }
                            }
                        } catch (error) {
                            this.listError = true;
                        } finally {
                            this.isLoadingList = false;
                            this.isLoadingMore = false;
                        }
                    },

                    async openConversation(id, options = {}) {
                        this.isLoadingDetail = true;
                        this.detailError = false;

                        try {
                            const response = await this.$axios.get(this.endpoint(this.detailEndpointTemplate, id), {
                                params: options.params || {},
                            });

                            this.applyConversationPayload(response.data, {
                                preserveScroll: options.preserveScroll || false,
                            });

                            const url = new URL(window.location.href);
                            url.searchParams.set('conversation', id);
                            window.history.replaceState({}, '', url.toString());
                        } catch (error) {
                            this.detailError = true;
                        } finally {
                            this.isLoadingDetail = false;
                        }
                    },

                    applyConversationPayload(payload, options = {}) {
                        const previousConversationId = this.selectedConversation ? this.selectedConversation.id : null;

                        this.selectedConversation = payload.conversation;
                        this.messages = payload.messages || [];
                        this.activities = payload.activities || [];
                        this.notes = payload.notes || [];
                        this.messageMeta = payload.message_meta || { has_more: false, next_before_id: null };
                        this.suggestedPersons = payload.suggested_persons || [];
                        this.suggestedLeads = payload.suggested_leads || [];
                        this.detailForm = {
                            status: payload.conversation.status || 'open',
                            assigned_user_id: payload.conversation.assigned_user_id ? String(payload.conversation.assigned_user_id) : '',
                            owner_user_id: payload.conversation.owner_user_id ? String(payload.conversation.owner_user_id) : '',
                            priority: payload.conversation.priority || 'medium',
                            follow_up_at: payload.conversation.follow_up_at || '',
                            next_action: payload.conversation.next_action || '',
                            person_id: payload.conversation.person_id ? String(payload.conversation.person_id) : '',
                            lead_id: payload.conversation.lead_id ? String(payload.conversation.lead_id) : '',
                        };
                        this.tagToAttach = '';

                        if (previousConversationId !== payload.conversation.id) {
                            this.composerDraft = '';
                        }

                        const conversationIndex = this.conversations.findIndex((item) => item.id === payload.conversation.id);

                        if (conversationIndex !== -1) {
                            this.conversations.splice(conversationIndex, 1, {
                                ...this.conversations[conversationIndex],
                                ...payload.conversation,
                            });
                        }

                        if (! options.preserveScroll) {
                            this.$nextTick(() => {
                                this.scrollThreadToBottom();
                            });
                        }
                    },

                    scrollThreadToBottom() {
                        if (! this.$refs.threadScroller) {
                            return;
                        }

                        this.$refs.threadScroller.scrollTop = this.$refs.threadScroller.scrollHeight;
                    },

                    async loadOlderMessages() {
                        if (! this.selectedConversation || ! this.messageMeta.has_more) {
                            return;
                        }

                        this.isLoadingMessages = true;

                        const scroller = this.$refs.threadScroller;
                        const previousHeight = scroller ? scroller.scrollHeight : 0;

                        try {
                            const response = await this.$axios.get(this.endpoint(this.detailEndpointTemplate, this.selectedConversation.id), {
                                params: {
                                    before_id: this.messageMeta.next_before_id,
                                    limit: 40,
                                },
                            });

                            this.messages = [...response.data.messages, ...this.messages];
                            this.messageMeta = response.data.message_meta;

                            this.$nextTick(() => {
                                if (scroller) {
                                    scroller.scrollTop = scroller.scrollHeight - previousHeight + scroller.scrollTop;
                                }
                            });
                        } catch (error) {
                            this.flash('error', "@lang('whatsapp::app.inbox.errors.load_messages')");
                        } finally {
                            this.isLoadingMessages = false;
                        }
                    },

                    async loadActivities() {
                        if (! this.selectedConversation) {
                            return;
                        }

                        this.isLoadingActivities = true;

                        try {
                            const response = await this.$axios.get(this.endpoint(this.activityEndpointTemplate, this.selectedConversation.id));
                            this.activities = response.data.data || [];
                            this.notes = this.activities.filter((activity) => activity.type === 'note');
                        } catch (error) {
                            this.activities = [];
                            this.notes = [];
                        } finally {
                            this.isLoadingActivities = false;
                        }
                    },

                    async refreshSelectedConversation(options = {}) {
                        if (! this.selectedConversation) {
                            return;
                        }

                        await this.openConversation(this.selectedConversation.id, {
                            preserveScroll: options.preserveScroll || false,
                        });
                    },

                    async saveConversation() {
                        if (! this.selectedConversation) {
                            return;
                        }

                        this.isSavingConversation = true;

                        try {
                            const response = await this.$axios.put(this.endpoint(this.saveEndpointTemplate, this.selectedConversation.id), this.detailForm);
                            this.applyConversationPayload(response.data.data, { preserveScroll: true });
                            this.flash('success', response.data.message);
                            await this.fetchConversations(true);
                        } catch (error) {
                            this.flash('error', error.response && error.response.data && error.response.data.message
                                ? error.response.data.message
                                : 'Unable to save the conversation.');
                        } finally {
                            this.isSavingConversation = false;
                        }
                    },

                    async saveNote() {
                        if (! this.selectedConversation || ! this.newNote.trim()) {
                            return;
                        }

                        try {
                            const response = await this.$axios.post(this.endpoint(this.noteEndpointTemplate, this.selectedConversation.id), {
                                comment: this.newNote,
                            });

                            this.newNote = '';
                            this.flash('success', response.data.message);
                            await this.refreshSelectedConversation({ preserveScroll: true });
                        } catch (error) {
                            this.flash('error', error.response && error.response.data && error.response.data.message
                                ? error.response.data.message
                                : 'Unable to save the note.');
                        }
                    },

                    async sendMessage() {
                        if (! this.composerCanSend || ! this.selectedConversation) {
                            return;
                        }

                        this.isSendingMessage = true;

                        try {
                            const response = await this.$axios.post(this.endpoint(this.messageStoreEndpointTemplate, this.selectedConversation.id), {
                                content: this.composerDraft.trim(),
                            });

                            this.composerDraft = '';
                            this.applyConversationPayload(response.data.data);
                            this.flash('success', response.data.message);
                            await this.fetchConversations(true);

                            this.$nextTick(() => {
                                if (this.$refs.composerTextarea) {
                                    this.$refs.composerTextarea.focus();
                                }
                            });
                        } catch (error) {
                            this.flash('error', error.response && error.response.data && error.response.data.message
                                ? error.response.data.message
                                : 'Unable to save the outgoing message.');
                        } finally {
                            this.isSendingMessage = false;
                        }
                    },

                    async createContact() {
                        await this.performDetailRequest(this.contactCreateEndpointTemplate, 'post');
                    },

                    async createLead() {
                        await this.performDetailRequest(this.leadCreateEndpointTemplate, 'post');
                    },

                    async toggleRead() {
                        await this.performDetailRequest(
                            this.selectedConversation.is_unread ? this.readEndpointTemplate : this.unreadEndpointTemplate,
                            'post'
                        );
                    },

                    async toggleArchive() {
                        await this.performDetailRequest(
                            this.selectedConversation.is_archived ? this.unarchiveEndpointTemplate : this.archiveEndpointTemplate,
                            'post',
                            {},
                            { refreshListOnly: true }
                        );
                    },

                    async togglePin() {
                        await this.performDetailRequest(
                            this.selectedConversation.is_pinned ? this.unpinEndpointTemplate : this.pinEndpointTemplate,
                            'post'
                        );
                    },

                    async toggleStatus() {
                        await this.performDetailRequest(
                            this.selectedConversation.status === 'closed' ? this.reopenEndpointTemplate : this.closeEndpointTemplate,
                            'post'
                        );
                    },

                    async deleteConversation() {
                        if (! this.selectedConversation) {
                            return;
                        }

                        this.$emitter.emit('open-confirm-modal', {
                            agree: async () => {
                                await this.performDetailRequest(this.deleteEndpointTemplate, 'delete', {}, { refreshListOnly: true });
                            },
                        });
                    },

                    async attachTag() {
                        if (! this.selectedConversation || ! this.tagToAttach) {
                            return;
                        }

                        try {
                            const response = await this.$axios.post(this.endpoint(this.tagAttachEndpointTemplate, this.selectedConversation.id), {
                                tag_id: this.tagToAttach,
                            });

                            this.flash('success', response.data.message);
                            await this.refreshSelectedConversation({ preserveScroll: true });
                            await this.fetchConversations(true);
                        } catch (error) {
                            this.flash('error', error.response && error.response.data && error.response.data.message
                                ? error.response.data.message
                                : 'Unable to attach the tag.');
                        }
                    },

                    async detachTag(tagId) {
                        if (! this.selectedConversation) {
                            return;
                        }

                        try {
                            const response = await this.$axios.delete(this.endpoint(this.tagDetachEndpointTemplate, this.selectedConversation.id), {
                                data: { tag_id: tagId },
                            });

                            this.flash('success', response.data.message);
                            await this.refreshSelectedConversation({ preserveScroll: true });
                            await this.fetchConversations(true);
                        } catch (error) {
                            this.flash('error', error.response && error.response.data && error.response.data.message
                                ? error.response.data.message
                                : 'Unable to remove the tag.');
                        }
                    },

                    async performDetailRequest(template, method = 'post', payload = {}, options = {}) {
                        if (! this.selectedConversation) {
                            return;
                        }

                        try {
                            const response = await this.$axios({
                                method,
                                url: this.endpoint(template, this.selectedConversation.id),
                                data: payload,
                            });

                            if (response.data.data) {
                                this.applyConversationPayload(response.data.data, { preserveScroll: true });
                            }

                            this.flash('success', response.data.message);
                            await this.fetchConversations(true);

                            if (options.refreshListOnly) {
                                const currentId = this.selectedConversation.id;
                                const stillVisible = this.conversations.find((conversation) => conversation.id === currentId);

                                if (stillVisible) {
                                    await this.openConversation(stillVisible.id, { preserveScroll: true });
                                } else if (this.conversations.length) {
                                    await this.openConversation(this.conversations[0].id);
                                } else {
                                    this.selectedConversation = null;
                                    this.messages = [];
                                    this.activities = [];
                                    this.notes = [];
                                }
                            }
                        } catch (error) {
                            this.flash('error', error.response && error.response.data && error.response.data.message
                                ? error.response.data.message
                                : 'Unable to update the conversation.');
                        }
                    },

                    focusNoteComposer() {
                        this.$nextTick(() => {
                            if (this.$refs.noteTextarea) {
                                this.$refs.noteTextarea.focus();
                            }
                        });
                    },

                    focusFollowUpField() {
                        this.$nextTick(() => {
                            if (this.$refs.followUpField) {
                                this.$refs.followUpField.focus();
                            }
                        });
                    },

                    focusTagPicker() {
                        this.$nextTick(() => {
                            if (this.$refs.tagPicker) {
                                this.$refs.tagPicker.focus();
                            }
                        });
                    },

                    toggleAssignmentFocus() {
                        this.$nextTick(() => {
                            if (this.$refs.assignedUserField) {
                                this.$refs.assignedUserField.focus();
                            }
                        });
                    },

                    showPlaceholder(feature) {
                        const label = feature === 'attachments'
                            ? 'Attachments'
                            : feature === 'emoji'
                                ? 'Emoji'
                                : 'Templates';

                        this.flash('info', `${label} will plug into the send flow later. The composer already stores plain text messages locally.`);
                    },

                    flash(type, message) {
                        this.$emitter.emit('add-flash', { type, message });
                    },

                    formatActivityDate(value) {
                        if (! value) {
                            return '';
                        }

                        return this.$admin.formatDate(value, 'd MMM yyyy, h:mm A', "{{ config('app.timezone') }}");
                    },
                },
            });
        </script>
    @endPushOnce
</div>
