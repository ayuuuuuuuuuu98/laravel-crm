<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('whatsapp_conversations')) {
            return;
        }

        Schema::table('whatsapp_conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_conversations', 'assigned_user_id')) {
                $table->unsignedInteger('assigned_user_id')->nullable()->after('lead_id');
                $table->foreign('assigned_user_id')->references('id')->on('users')->nullOnDelete();
            }

            $table->index(['status', 'last_message_at'], 'wa_conversations_status_last_message_idx');
            $table->index(['assigned_user_id', 'status'], 'wa_conversations_assigned_status_idx');
        });

        if (Schema::hasTable('whatsapp_messages')) {
            Schema::table('whatsapp_messages', function (Blueprint $table) {
                $table->index(['conversation_id', 'created_at'], 'wa_messages_conversation_created_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('whatsapp_messages')) {
            Schema::table('whatsapp_messages', function (Blueprint $table) {
                $table->dropIndex('wa_messages_conversation_created_idx');
            });
        }

        if (! Schema::hasTable('whatsapp_conversations')) {
            return;
        }

        Schema::table('whatsapp_conversations', function (Blueprint $table) {
            $table->dropIndex('wa_conversations_status_last_message_idx');
            $table->dropIndex('wa_conversations_assigned_status_idx');

            if (Schema::hasColumn('whatsapp_conversations', 'assigned_user_id')) {
                $table->dropForeign(['assigned_user_id']);
                $table->dropColumn('assigned_user_id');
            }
        });
    }
};
