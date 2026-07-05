<?php

use Illuminate\Support\Facades\Route;
use Webkul\WhatsApp\Http\Controllers\Admin\ActivityController;
use Webkul\WhatsApp\Http\Controllers\Admin\InboxController;
use Webkul\WhatsApp\Http\Controllers\Admin\SettingsController;
use Webkul\WhatsApp\Http\Controllers\Admin\TagController;

Route::middleware(['web', 'admin_locale', 'user'])
    ->prefix(config('app.admin_path'))
    ->group(function () {
        Route::controller(InboxController::class)->prefix('whatsapp')->group(function () {
            Route::get('inbox', 'index')->name('admin.whatsapp.inbox.index');
            Route::get('inbox/list', 'list')->name('admin.whatsapp.inbox.list');
            Route::get('inbox/{id}/show', 'show')->name('admin.whatsapp.inbox.show');
            Route::put('inbox/{id}/save', 'save')->name('admin.whatsapp.inbox.save');
            Route::get('inbox/{id}/messages', 'show')->name('admin.whatsapp.inbox.messages');
            Route::post('inbox/{id}/messages', 'sendMessage')->name('admin.whatsapp.inbox.messages.store');
            Route::get('inbox/{id}', 'view')->name('admin.whatsapp.inbox.view');
            Route::put('inbox/{id}', 'update')->name('admin.whatsapp.inbox.update');
            Route::post('inbox/{id}/lead', 'createLead')->name('admin.whatsapp.inbox.lead.create');
            Route::post('inbox/{id}/lead/ajax', 'createLeadAjax')->name('admin.whatsapp.inbox.lead.create_ajax');
            Route::post('inbox/{id}/contact', 'createContact')->name('admin.whatsapp.inbox.contact.create');
            Route::post('inbox/{id}/notes', 'storeNote')->name('admin.whatsapp.inbox.notes.store');
            Route::post('inbox/{id}/notes/ajax', 'storeNoteAjax')->name('admin.whatsapp.inbox.notes.store_ajax');
            Route::post('inbox/{id}/read', 'markRead')->name('admin.whatsapp.inbox.read');
            Route::post('inbox/{id}/unread', 'markUnread')->name('admin.whatsapp.inbox.unread');
            Route::post('inbox/{id}/archive', 'archive')->name('admin.whatsapp.inbox.archive');
            Route::post('inbox/{id}/unarchive', 'unarchive')->name('admin.whatsapp.inbox.unarchive');
            Route::post('inbox/{id}/pin', 'pin')->name('admin.whatsapp.inbox.pin');
            Route::post('inbox/{id}/unpin', 'unpin')->name('admin.whatsapp.inbox.unpin');
            Route::post('inbox/{id}/close', 'close')->name('admin.whatsapp.inbox.close');
            Route::post('inbox/{id}/reopen', 'reopen')->name('admin.whatsapp.inbox.reopen');
            Route::delete('inbox/{id}', 'destroy')->name('admin.whatsapp.inbox.delete');
            Route::post('inbox/{id}/restore', 'restore')->name('admin.whatsapp.inbox.restore');
        });

        Route::controller(SettingsController::class)->prefix('whatsapp')->group(function () {
            Route::get('settings', 'index')->name('admin.whatsapp.settings.index');
            Route::post('settings', 'store')->name('admin.whatsapp.settings.store');
        });

        Route::controller(ActivityController::class)->prefix('whatsapp/inbox/{id}/activities')->group(function () {
            Route::get('', 'index')->name('admin.whatsapp.inbox.activities.index');
        });

        Route::controller(TagController::class)->prefix('whatsapp/inbox/{id}/tags')->group(function () {
            Route::post('', 'attach')->name('admin.whatsapp.inbox.tags.attach');
            Route::delete('', 'detach')->name('admin.whatsapp.inbox.tags.detach');
        });
    });
