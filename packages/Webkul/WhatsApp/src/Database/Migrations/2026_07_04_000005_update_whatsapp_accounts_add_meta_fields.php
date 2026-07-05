<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('whatsapp_accounts')) {
            return;
        }

        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_accounts', 'meta_app_id')) {
                $table->string('meta_app_id')->nullable()->after('provider');
            }

            if (! Schema::hasColumn('whatsapp_accounts', 'meta_app_secret')) {
                $table->text('meta_app_secret')->nullable()->after('meta_app_id');
            }

            if (! Schema::hasColumn('whatsapp_accounts', 'verify_token')) {
                $table->string('verify_token')->nullable()->after('access_token');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('whatsapp_accounts')) {
            return;
        }

        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_accounts', 'meta_app_id')) {
                $table->dropColumn('meta_app_id');
            }

            if (Schema::hasColumn('whatsapp_accounts', 'meta_app_secret')) {
                $table->dropColumn('meta_app_secret');
            }

            if (Schema::hasColumn('whatsapp_accounts', 'verify_token')) {
                $table->dropColumn('verify_token');
            }
        });
    }
};
