<?php

namespace Webkul\ScalvionFoundation\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;
use Webkul\Lead\Models\Lead;
use Webkul\ScalvionFoundation\Observers\EntityObserver;
use Webkul\ScalvionFoundation\Support\AuditLogger;
use Webkul\ScalvionFoundation\Support\EntityResolver;
use Webkul\ScalvionFoundation\Support\TimelineAggregator;
use Webkul\WhatsApp\Models\WhatsAppConversation;

class ScalvionFoundationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/admin.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'scalvion-foundation');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'scalvion-foundation');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->registerViewHooks();
        $this->registerObservers();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/menu.php', 'menu.admin');
        $this->mergeConfigFrom(__DIR__.'/../Config/acl.php', 'acl');

        $this->app->singleton(EntityResolver::class);
        $this->app->singleton(AuditLogger::class);
        $this->app->singleton(TimelineAggregator::class);
    }

    protected function registerViewHooks(): void
    {
        Event::listen('admin.layout.content.before', function ($manager) {
            $manager->addTemplate('scalvion-foundation::admin.notifications.bell');
        });

        Event::listen('admin.dashboard.index.content.right.after', function ($manager) {
            $manager->addTemplate('scalvion-foundation::admin.dashboard.upcoming-followups');
        });

        Event::listen('admin.leads.view.right.after', function ($manager) {
            $manager->addTemplate('scalvion-foundation::admin.entities.lead-panels');
        });

        Event::listen('admin.contact.persons.view.right.after', function ($manager) {
            $manager->addTemplate('scalvion-foundation::admin.entities.person-panels');
        });

        Event::listen('admin.organizations.edit.form.after', function ($manager) {
            $manager->addTemplate('scalvion-foundation::admin.entities.organization-panels');
        });
    }

    protected function registerObservers(): void
    {
        Person::observe(EntityObserver::class);
        Organization::observe(EntityObserver::class);
        Lead::observe(EntityObserver::class);
        WhatsAppConversation::observe(EntityObserver::class);
    }
}
