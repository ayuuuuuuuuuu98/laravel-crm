<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scalvion_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->morphs('followupable');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('note')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('remind_at')->nullable();
            $table->timestamp('snoozed_until')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'remind_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scalvion_follow_ups');
    }
};
