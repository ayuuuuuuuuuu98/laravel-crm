<?php

namespace Webkul\WhatsApp\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\WhatsApp\Console\Commands\SeedDemoDataCommand;

class WhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/admin.php');

        $this->loadRoutesFrom(__DIR__.'/../Routes/webhook.php');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'whatsapp');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'whatsapp');

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SeedDemoDataCommand::class,
            ]);
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/menu.php', 'menu.admin');

        $this->mergeConfigFrom(__DIR__.'/../Config/acl.php', 'acl');
    }
}
