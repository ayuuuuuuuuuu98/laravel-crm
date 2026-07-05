<?php

use Illuminate\Support\Facades\Route;
use Webkul\WhatsApp\Http\Controllers\WebhookController;

Route::prefix('whatsapp')->group(function () {
    Route::get('webhook', [WebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
    Route::post('webhook', [WebhookController::class, 'receive'])->name('whatsapp.webhook.receive');
});
