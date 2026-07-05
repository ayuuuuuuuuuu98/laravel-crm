<x-admin::layouts>
    <x-slot:title>
        Search: {{ $term ?: 'All Results' }}
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] rounded-lg border border-gray-300 bg-white px-4 py-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h1 class="text-xl font-bold dark:text-white">
                Search Results
            </h1>
            <form method="GET" action="{{ route('admin.scalvion.search.page') }}" class="mt-2 flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $term }}"
                    placeholder="Search contacts, leads, companies, conversations..."
                    class="w-full max-w-md rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    autofocus
                />
                <button type="submit" class="primary-button text-sm">Search</button>
            </form>
        </div>

        @if (strlen($term) < 2)
            <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700">
                Enter at least 2 characters to search.
            </div>
        @else
            <div class="grid gap-4 xl:grid-cols-2">
                {{-- Contacts --}}
                <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="mb-3 text-base font-semibold dark:text-white">Contacts ({{ $contacts->total() }})</h2>
                    @if ($contacts->count())
                        <div class="space-y-2">
                            @foreach ($contacts as $person)
                                <a href="{{ route('admin.contacts.persons.view', $person->id) }}" class="block rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950">
                                    <p class="font-semibold dark:text-white">{{ $person->name }}</p>
                                    @if ($person->emails)
                                        <p class="text-xs text-gray-500">{{ $person->emails }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            {{ $contacts->appends(['q' => $term])->fragment('')->links() }}
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No contacts found.</p>
                    @endif
                </div>

                {{-- Companies --}}
                <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="mb-3 text-base font-semibold dark:text-white">Companies ({{ $companies->total() }})</h2>
                    @if ($companies->count())
                        <div class="space-y-2">
                            @foreach ($companies as $company)
                                <a href="{{ route('admin.contacts.organizations.edit', $company->id) }}" class="block rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950">
                                    <p class="font-semibold dark:text-white">{{ $company->name }}</p>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            {{ $companies->appends(['q' => $term])->fragment('')->links() }}
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No companies found.</p>
                    @endif
                </div>

                {{-- Leads --}}
                <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="mb-3 text-base font-semibold dark:text-white">Leads ({{ $leads->total() }})</h2>
                    @if ($leads->count())
                        <div class="space-y-2">
                            @foreach ($leads as $lead)
                                <a href="{{ route('admin.leads.view', $lead->id) }}" class="block rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950">
                                    <p class="font-semibold dark:text-white">{{ $lead->title }}</p>
                                    @if ($lead->description)
                                        <p class="mt-1 text-xs text-gray-500">{{ Str::limit($lead->description, 120) }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            {{ $leads->appends(['q' => $term])->fragment('')->links() }}
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No leads found.</p>
                    @endif
                </div>

                {{-- WhatsApp Conversations --}}
                <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="mb-3 text-base font-semibold dark:text-white">WhatsApp Conversations ({{ $conversations->total() }})</h2>
                    @if ($conversations->count())
                        <div class="space-y-2">
                            @foreach ($conversations as $conversation)
                                <a href="{{ route('admin.whatsapp.inbox.view', $conversation->id) }}" class="block rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950">
                                    <p class="font-semibold dark:text-white">{{ $conversation->contact_identifier }}</p>
                                    <span class="mt-1 inline-block rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $conversation->status }}</span>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            {{ $conversations->appends(['q' => $term])->fragment('')->links() }}
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No conversations found.</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-admin::layouts>
