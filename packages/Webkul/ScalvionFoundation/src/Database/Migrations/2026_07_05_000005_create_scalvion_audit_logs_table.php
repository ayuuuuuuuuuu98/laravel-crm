<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scalvion_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('auditable');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scalvion_audit_logs');
    }
};
