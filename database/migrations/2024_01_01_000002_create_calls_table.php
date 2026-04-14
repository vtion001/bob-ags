<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ctm_call_id')->unique();
            $table->string('tracking_number', 20)->nullable();
            $table->string('tracking_label')->nullable();
            $table->string('caller_number', 20)->nullable();
            $table->string('caller_city', 100)->nullable();
            $table->string('caller_state', 50)->nullable();
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->string('source', 100)->nullable();
            $table->string('agent_name', 100)->nullable();
            $table->string('agent_id', 50)->nullable();
            $table->string('recording_url')->nullable();
            $table->string('transcript_url')->nullable();
            $table->text('transcript_text')->nullable();
            $table->json('transcript_json')->nullable();
            $table->string('transcript_id')->nullable();
            $table->enum('status', ['pending', 'transcribed', 'analyzing', 'analyzed', 'completed'])->default('pending');
            $table->timestamp('call_datetime');
            $table->integer('duration')->default(0);
            $table->timestamps();

            $table->index(['status', 'call_datetime']);
            $table->index(['caller_number', 'call_datetime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
