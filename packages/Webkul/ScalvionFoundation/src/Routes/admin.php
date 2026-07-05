<?php

use Illuminate\Support\Facades\Route;
use Webkul\ScalvionFoundation\Http\Controllers\Admin\AuditLogController;
use Webkul\ScalvionFoundation\Http\Controllers\Admin\FollowUpController;
use Webkul\ScalvionFoundation\Http\Controllers\Admin\NotificationController;
use Webkul\ScalvionFoundation\Http\Controllers\Admin\SearchController;
use Webkul\ScalvionFoundation\Http\Controllers\Admin\TimelineController;

Route::middleware(['web', 'admin_locale', 'user'])
    ->prefix(config('app.admin_path'))
    ->group(function () {
        Route::prefix('foundation')->group(function () {
            Route::controller(NotificationController::class)->prefix('notifications')->group(function () {
                Route::get('summary', 'summary')->name('admin.scalvion.notifications.summary');
                Route::get('unread', 'unread')->name('admin.scalvion.notifications.unread');
                Route::post('{id}/read', 'markRead')->name('admin.scalvion.notifications.mark_read');
                Route::post('mark-all-read', 'markAllRead')->name('admin.scalvion.notifications.mark_all_read');
                Route::get('history', 'history')->name('admin.scalvion.notifications.history');
                Route::get('preferences', 'preferences')->name('admin.scalvion.notifications.preferences');
                Route::post('preferences', 'storePreferences')->name('admin.scalvion.notifications.preferences.store');
            });

            Route::get('search', [SearchController::class, 'index'])->name('admin.scalvion.search.index');
            Route::get('search/page', [SearchController::class, 'page'])->name('admin.scalvion.search.page');

            Route::controller(FollowUpController::class)->prefix('follow-ups')->group(function () {
                Route::get('overview', 'overview')->name('admin.scalvion.follow-ups.overview');
                Route::get('upcoming', 'upcoming')->name('admin.scalvion.follow-ups.upcoming');
                Route::get('{entityType}/{entityId}', 'index')->name('admin.scalvion.follow-ups.index');
                Route::post('', 'store')->name('admin.scalvion.follow-ups.store');
                Route::put('{id}', 'update')->name('admin.scalvion.follow-ups.update');
                Route::post('{id}/complete', 'complete')->name('admin.scalvion.follow-ups.complete');
                Route::post('{id}/snooze', 'snooze')->name('admin.scalvion.follow-ups.snooze');
            });

            Route::get('timeline/{entityType}/{entityId}', [TimelineController::class, 'index'])->name('admin.scalvion.timeline.index');

            Route::controller(AuditLogController::class)->prefix('audit-logs')->group(function () {
                Route::get('', 'index')->name('admin.scalvion.audit-logs.index');
                Route::get('{entityType}/{entityId}', 'entity')->name('admin.scalvion.audit-logs.entity');
            });
        });
    });
