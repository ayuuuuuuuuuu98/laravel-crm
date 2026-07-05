<x-admin::layouts>
    <x-slot:title>
        {{ __('whatsapp::app.inbox.title') }}
    </x-slot:title>

    @include('whatsapp::admin.inbox.workspace', [
        'selectedConversationId' => $selectedConversationId ?? null,
        'users'                  => $users ?? collect(),
        'tags'                   => $tags ?? collect(),
    ])
</x-admin::layouts>
