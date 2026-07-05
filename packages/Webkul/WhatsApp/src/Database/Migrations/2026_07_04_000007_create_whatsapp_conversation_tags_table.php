<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_conversation_tags')) {
            return;
        }

        Schema::create('whatsapp_conversation_tags', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->unsignedInteger('whatsapp_conversation_id');
            $table->foreign('whatsapp_conversation_id')->references('id')->on('whatsapp_conversations')->onDelete('cascade');
            $table->unsignedInteger('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->unique(['whatsapp_conversation_id', 'tag_id'], 'wa_conversation_tag_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversation_tags');
    }
};
