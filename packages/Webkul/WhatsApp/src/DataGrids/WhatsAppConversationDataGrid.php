<?php

namespace Webkul\WhatsApp\DataGrids;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Tag\Repositories\TagRepository;
use Webkul\User\Repositories\UserRepository;

class WhatsAppConversationDataGrid extends DataGrid
{
    protected $sortColumn = 'last_message_at';

    public function __construct(
        protected UserRepository $userRepository,
        protected TagRepository $tagRepository,
    ) {}

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('whatsapp_conversations')
            ->select(
                'whatsapp_conversations.id',
                'whatsapp_conversations.contact_identifier',
                'whatsapp_conversations.status',
                'whatsapp_conversations.unread_count',
                'whatsapp_conversations.last_message_at',
                'whatsapp_conversations.assigned_user_id',
                'persons.name as person_name',
                'persons.id as person_id',
                'leads.id as lead_id',
                'leads.title as lead_title',
                'users.name as assigned_user_name',
                DB::raw('COALESCE(persons.name, leads.title, whatsapp_conversations.contact_identifier) as contact'),
                DB::raw("(SELECT content FROM whatsapp_messages wm WHERE wm.conversation_id = whatsapp_conversations.id ORDER BY wm.created_at DESC, wm.id DESC LIMIT 1) as last_message_preview"),
                DB::raw("GROUP_CONCAT(DISTINCT tags.name SEPARATOR ', ') as tag_names")
            )
            ->leftJoin('persons', 'whatsapp_conversations.person_id', '=', 'persons.id')
            ->leftJoin('leads', 'whatsapp_conversations.lead_id', '=', 'leads.id')
            ->leftJoin('users', 'whatsapp_conversations.assigned_user_id', '=', 'users.id')
            ->leftJoin('whatsapp_conversation_tags', 'whatsapp_conversations.id', '=', 'whatsapp_conversation_tags.whatsapp_conversation_id')
            ->leftJoin('tags', 'whatsapp_conversation_tags.tag_id', '=', 'tags.id')
            ->groupBy(
                'whatsapp_conversations.id',
                'whatsapp_conversations.contact_identifier',
                'whatsapp_conversations.status',
                'whatsapp_conversations.unread_count',
                'whatsapp_conversations.last_message_at',
                'whatsapp_conversations.assigned_user_id',
                'persons.name',
                'persons.id',
                'leads.id',
                'leads.title',
                'users.name'
            );

        $this->addFilter('contact', DB::raw('COALESCE(persons.name, leads.title, whatsapp_conversations.contact_identifier)'));
        $this->addFilter('status', 'whatsapp_conversations.status');
        $this->addFilter('last_message_at', 'whatsapp_conversations.last_message_at');
        $this->addFilter('unread_count', 'whatsapp_conversations.unread_count');
        $this->addFilter('assigned_user_name', 'users.name');
        $this->addFilter('tag_names', 'tags.name');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'contact',
            'label' => trans('whatsapp::app.inbox.datagrid.contact'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                $route = route('admin.whatsapp.inbox.view', $row->id);
                $preview = $row->last_message_preview
                    ? e(str($row->last_message_preview)->limit(80)->toString())
                    : e(trans('whatsapp::app.inbox.datagrid.no_preview'));

                return '<a class="flex flex-col gap-1 text-brandColor transition-all hover:underline" href="'.$route.'">'
                    .'<span class="font-semibold">'.$row->contact.'</span>'
                    .'<span class="text-xs text-gray-500">'.$preview.'</span>'
                    .'</a>';
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('whatsapp::app.inbox.datagrid.status'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
            'closure' => fn ($row) => trans('whatsapp::app.inbox.statuses.' . $row->status),
        ]);

        $this->addColumn([
            'index' => 'assigned_user_name',
            'label' => trans('whatsapp::app.inbox.datagrid.assigned_to'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => UserRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
            'closure' => fn ($row) => $row->assigned_user_name ?: '--',
        ]);

        $this->addColumn([
            'index' => 'unread_count',
            'label' => trans('whatsapp::app.inbox.datagrid.unread'),
            'type' => 'integer',
            'sortable' => true,
            'searchable' => false,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index' => 'tag_names',
            'label' => trans('whatsapp::app.inbox.datagrid.tags'),
            'type' => 'string',
            'sortable' => false,
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => TagRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
            'closure' => fn ($row) => $row->tag_names ?: '--',
        ]);

        $this->addColumn([
            'index' => 'last_message_at',
            'label' => trans('whatsapp::app.inbox.datagrid.last_message'),
            'type' => 'date',
            'sortable' => true,
            'searchable' => false,
            'filterable' => true,
            'closure' => fn ($row) => $row->last_message_at ? Carbon::parse($row->last_message_at)->format('M d, Y H:i') : '--',
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'icon' => 'icon-eye',
            'title' => trans('whatsapp::app.inbox.datagrid.view'),
            'method' => 'GET',
            'url' => fn ($row) => route('admin.whatsapp.inbox.view', $row->id),
        ]);
    }
}
