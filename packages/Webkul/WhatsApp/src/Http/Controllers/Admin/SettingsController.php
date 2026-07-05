<?php

namespace Webkul\WhatsApp\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\WhatsApp\Models\WhatsAppAccount;

class SettingsController extends Controller
{
    public function index(): View
    {
        $account = WhatsAppAccount::orderBy('id')->first();

        return view('whatsapp::admin.settings.index', [
            'account' => $account,
            'webhookUrl' => url('whatsapp/webhook'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'meta_app_id' => ['nullable', 'string', 'max:255'],
            'meta_app_secret' => ['nullable', 'string'],
            'business_account_id' => ['nullable', 'string', 'max:255'],
            'phone_number_id' => ['nullable', 'string', 'max:255'],
            'access_token' => ['nullable', 'string'],
            'verify_token' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        if ($request->filled('id')) {
            $account = WhatsAppAccount::find($request->input('id'));

            if ($account) {
                $account->update($data);
            } else {
                $account = WhatsAppAccount::create(array_merge(
                    [
                        'name' => 'Meta WhatsApp Account',
                        'provider' => 'meta',
                    ],
                    $data
                ));
            }
        } else {
            $account = WhatsAppAccount::create(array_merge(
                [
                    'name' => 'Meta WhatsApp Account',
                    'provider' => 'meta',
                ],
                $data
            ));
        }

        return redirect()
            ->route('admin.whatsapp.settings.index')
            ->with('success', trans('whatsapp::app.settings.save-success'));
    }
}
