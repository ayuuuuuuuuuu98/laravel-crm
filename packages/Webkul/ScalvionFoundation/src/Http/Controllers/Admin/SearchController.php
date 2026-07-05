<?php

namespace Webkul\ScalvionFoundation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;
use Webkul\Lead\Models\Lead;
use Webkul\WhatsApp\Models\WhatsAppConversation;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));
        $limit = min((int) $request->input('limit', 5), 15);

        if (strlen($term) < 2) {
            return response()->json([
                'query'   => $term,
                'results' => [
                    'contacts'               => [],
                    'companies'              => [],
                    'leads'                  => [],
                    'deals'                  => [],
                    'whatsapp_conversations' => [],
                ],
            ]);
        }

        $termLike = "%{$term}%";

        $contacts = Person::query()
            ->where('name', 'like', $termLike)
            ->orWhere('emails', 'like', $termLike)
            ->orWhere('contact_numbers', 'like', $termLike)
            ->limit($limit)
            ->get(['id', 'name', 'emails', 'contact_numbers']);

        $companies = Organization::query()
            ->where('name', 'like', $termLike)
            ->limit($limit)
            ->get(['id', 'name']);

        $leads = Lead::query()
            ->where('title', 'like', $termLike)
            ->orWhere('description', 'like', $termLike)
            ->limit($limit)
            ->get(['id', 'title', 'description', 'lead_value', 'status']);

        $conversations = WhatsAppConversation::query()
            ->where('contact_identifier', 'like', $termLike)
            ->limit($limit)
            ->get(['id', 'contact_identifier', 'status', 'priority']);

        return response()->json([
            'query'   => $term,
            'results' => [
                'contacts' => $contacts->map(fn ($person) => [
                    'id'     => $person->id,
                    'name'   => $person->name,
                    'emails' => $person->emails,
                    'url'    => route('admin.contacts.persons.view', $person->id),
                ]),
                'companies' => $companies->map(fn ($organization) => [
                    'id'   => $organization->id,
                    'name' => $organization->name,
                    'url'  => route('admin.contacts.organizations.edit', $organization->id),
                ]),
                'leads' => $leads->map(fn ($lead) => [
                    'id'          => $lead->id,
                    'title'       => $lead->title,
                    'description' => $lead->description,
                    'value'       => $lead->lead_value,
                    'status'      => $lead->status,
                    'url'         => route('admin.leads.view', $lead->id),
                ]),
                'deals' => $leads->map(fn ($lead) => [
                    'id'     => $lead->id,
                    'title'  => $lead->title,
                    'status' => $lead->status,
                    'url'    => route('admin.leads.view', $lead->id),
                ]),
                'whatsapp_conversations' => $conversations->map(fn ($conversation) => [
                    'id'                 => $conversation->id,
                    'contact_identifier' => $conversation->contact_identifier,
                    'status'             => $conversation->status,
                    'url'                => route('admin.whatsapp.inbox.view', $conversation->id),
                ]),
            ],
        ]);
    }

    public function page(Request $request): View
    {
        $term = trim((string) $request->input('q', ''));
        $perPage = min((int) $request->input('per_page', 15), 50);

        $contacts = collect();
        $companies = collect();
        $leads = collect();
        $conversations = collect();

        if (strlen($term) >= 2) {
            $termLike = "%{$term}%";

            $contacts = Person::query()
                ->where('name', 'like', $termLike)
                ->orWhere('emails', 'like', $termLike)
                ->orWhere('contact_numbers', 'like', $termLike)
                ->paginate($perPage, ['id', 'name', 'emails', 'contact_numbers'], 'contacts_page');

            $companies = Organization::query()
                ->where('name', 'like', $termLike)
                ->paginate($perPage, ['id', 'name'], 'companies_page');

            $leads = Lead::query()
                ->where('title', 'like', $termLike)
                ->orWhere('description', 'like', $termLike)
                ->paginate($perPage, ['id', 'title', 'description', 'lead_value', 'status'], 'leads_page');

            $conversations = WhatsAppConversation::query()
                ->where('contact_identifier', 'like', $termLike)
                ->paginate($perPage, ['id', 'contact_identifier', 'status', 'priority'], 'conversations_page');
        }

        return view('scalvion-foundation::admin.search.index', compact(
            'term', 'contacts', 'companies', 'leads', 'conversations'
        ));
    }
}
