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
        if (Schema::hasTable('whatsapp_messages')) {
            return;
        }

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->unsignedInteger('conversation_id');
            $table->foreign('conversation_id')->references('id')->on('whatsapp_conversations')->cascadeOnDelete();
            $table->unsignedInteger('account_id');
            $table->foreign('account_id')->references('id')->on('whatsapp_accounts')->cascadeOnDelete();
            $table->string('direction')->default('inbound');
            $table->string('message_type')->default('text');
            $table->text('content')->nullable();
            $table->string('external_message_id')->nullable();
            $table->string('status')->default('queued');
            $table->string('sender_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
