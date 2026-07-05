<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_webhook_events')) {
            return;
        }

        Schema::create('whatsapp_webhook_events', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->unsignedInteger('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('whatsapp_accounts')->nullOnDelete();
            $table->string('event_type')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_webhook_events');
    }
};
