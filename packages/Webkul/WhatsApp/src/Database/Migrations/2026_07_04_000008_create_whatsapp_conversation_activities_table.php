<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('whatsapp_conversation_activities');

        Schema::create('whatsapp_conversation_activities', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->unsignedInteger('whatsapp_conversation_id');
            $table->foreign('whatsapp_conversation_id', 'wa_conv_activities_conv_fk')
                ->references('id')
                ->on('whatsapp_conversations')
                ->onDelete('cascade');
            $table->unsignedInteger('activity_id');
            $table->foreign('activity_id', 'wa_conv_activities_activity_fk')
                ->references('id')
                ->on('activities')
                ->onDelete('cascade');
            $table->unique(['whatsapp_conversation_id', 'activity_id'], 'wa_conversation_activity_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversation_activities');
    }
};
