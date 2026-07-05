<?php

namespace Webkul\WhatsApp\Console\Commands;

use Illuminate\Console\Command;
use Webkul\WhatsApp\Database\Seeders\WhatsAppDemoSeeder;

class SeedDemoDataCommand extends Command
{
    protected $signature = 'whatsapp:seed-demo {--fresh : Skip the existing-record guard only when you explicitly want to append demo data}';

    protected $description = 'Seed safe demo WhatsApp inbox data for development environments.';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->line('Fresh mode enabled. Existing WhatsApp records are preserved, and additional demo-safe data may be appended.');
        } else {
            $this->line('Ensuring demo conversations, labels, and messages exist without wiping existing WhatsApp data.');
        }

        $this->call('db:seed', [
            '--class' => WhatsAppDemoSeeder::class,
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}
