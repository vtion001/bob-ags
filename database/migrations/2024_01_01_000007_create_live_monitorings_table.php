<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_monitorings', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('ctm_call_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('agent_name', 100)->nullable();
            $table->string('caller_number', 20)->nullable();
            $table->enum('status', ['active', 'paused', 'ended', 'error'])->default('active');
            $table->text('transcript_text')->nullable();
            $table->json('current_context')->nullable();
            $table->json('active_suggestions')->nullable();
            $table->json('ztp_alerts')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_monitorings');
    }
};
