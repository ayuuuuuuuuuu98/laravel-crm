<?php

namespace Webkul\WhatsApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Webkul\WhatsApp\Models\WhatsAppAccount;
use Webkul\WhatsApp\Models\WhatsAppWebhookEvent;

class WebhookController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->query('hub.mode') ?? $request->query('hub_mode');
        $challenge = $request->query('hub.challenge') ?? $request->query('hub_challenge');
        $verifyToken = $request->query('hub.verify_token') ?? $request->query('hub_verify_token');

        if ($mode !== 'subscribe' || ! $verifyToken || ! $challenge) {
            return response('Forbidden', 403);
        }

        $account = WhatsAppAccount::where('verify_token', $verifyToken)->first();

        if (! $account) {
            Log::warning('WhatsApp webhook verification failed: invalid verify token', [
                'verify_token' => $verifyToken,
            ]);

            return response('Forbidden', 403);
        }

        return response($challenge, 200);
    }

    public function receive(Request $request)
    {
        $payload = $request->all();
        $eventType = data_get($payload, 'object', 'unknown');
        $accountId = $this->findAccountId($payload);

        try {
            if (empty($payload)) {
                return response()->json(['error' => 'Empty payload'], 400);
            }

            WhatsAppWebhookEvent::create([
                'account_id' => $accountId,
                'event_type' => $eventType,
                'payload' => $payload,
                'success' => true,
            ]);

            return response()->json(['status' => 'EVENT_RECEIVED'], 200);
        } catch (\Throwable $exception) {
            Log::error('WhatsApp webhook receiver failed', [
                'message' => $exception->getMessage(),
                'payload' => $payload,
            ]);

            WhatsAppWebhookEvent::create([
                'account_id' => $accountId,
                'event_type' => $eventType,
                'payload' => $payload,
                'success' => false,
                'error_message' => $exception->getMessage(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    protected function findAccountId(array $payload): ?int
    {
        $phoneNumberId = data_get($payload, 'entry.0.changes.0.value.metadata.phone_number_id');
        $businessAccountId = data_get($payload, 'entry.0.changes.0.value.metadata.business_account_id');

        if ($phoneNumberId) {
            $account = WhatsAppAccount::where('phone_number_id', $phoneNumberId)->first();

            if ($account) {
                return $account->id;
            }
        }

        if ($businessAccountId) {
            $account = WhatsAppAccount::where('business_account_id', $businessAccountId)->first();

            if ($account) {
                return $account->id;
            }
        }

        return null;
    }
}
