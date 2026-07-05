<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scalvion_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('reminder_notifications')->default(true);
            $table->boolean('activity_notifications')->default(true);
            $table->boolean('audit_notifications')->default(true);
            $table->boolean('whatsapp_notifications')->default(true);
            $table->json('channels')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scalvion_notification_preferences');
    }
};
