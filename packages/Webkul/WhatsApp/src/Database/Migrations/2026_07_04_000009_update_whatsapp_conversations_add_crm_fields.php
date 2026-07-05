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
            if (! Schema::hasColumn('whatsapp_conversations', 'owner_user_id')) {
                $table->unsignedInteger('owner_user_id')->nullable()->after('assigned_user_id');
                $table->foreign('owner_user_id', 'wa_conversations_owner_fk')->references('id')->on('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('whatsapp_conversations', 'priority')) {
                $table->string('priority')->default('medium')->after('status');
            }

            if (! Schema::hasColumn('whatsapp_conversations', 'next_action')) {
                $table->string('next_action')->nullable()->after('priority');
            }

            if (! Schema::hasColumn('whatsapp_conversations', 'follow_up_at')) {
                $table->timestamp('follow_up_at')->nullable()->after('next_action');
            }

            if (! Schema::hasColumn('whatsapp_conversations', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable()->after('follow_up_at');
            }

            if (! Schema::hasColumn('whatsapp_conversations', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('pinned_at');
            }

            if (! Schema::hasColumn('whatsapp_conversations', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }

            $table->index(['archived_at', 'deleted_at'], 'wa_conversations_archive_delete_idx');
            $table->index(['priority', 'follow_up_at'], 'wa_conversations_priority_followup_idx');
            $table->index(['owner_user_id', 'assigned_user_id'], 'wa_conversations_owner_assigned_idx');
        });

        if (Schema::hasTable('whatsapp_messages')) {
            Schema::table('whatsapp_messages', function (Blueprint $table) {
                $table->index(['external_message_id', 'account_id'], 'wa_messages_external_account_idx');
                $table->index(['status', 'direction'], 'wa_messages_status_direction_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('whatsapp_messages')) {
            Schema::table('whatsapp_messages', function (Blueprint $table) {
                $table->dropIndex('wa_messages_external_account_idx');
                $table->dropIndex('wa_messages_status_direction_idx');
            });
        }

        if (! Schema::hasTable('whatsapp_conversations')) {
            return;
        }

        Schema::table('whatsapp_conversations', function (Blueprint $table) {
            $table->dropIndex('wa_conversations_archive_delete_idx');
            $table->dropIndex('wa_conversations_priority_followup_idx');
            $table->dropIndex('wa_conversations_owner_assigned_idx');

            if (Schema::hasColumn('whatsapp_conversations', 'owner_user_id')) {
                $table->dropForeign('wa_conversations_owner_fk');
                $table->dropColumn('owner_user_id');
            }

            foreach (['priority', 'next_action', 'follow_up_at', 'pinned_at', 'archived_at', 'deleted_at'] as $column) {
                if (Schema::hasColumn('whatsapp_conversations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
