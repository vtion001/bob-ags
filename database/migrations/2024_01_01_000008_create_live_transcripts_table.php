<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_monitoring_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->string('speaker', 50)->default('unknown');
            $table->float('confidence')->nullable();
            $table->float('start_time')->nullable();
            $table->float('end_time')->nullable();
            $table->boolean('is_final')->default(true);
            $table->timestamps();

            $table->index('live_monitoring_id');
            $table->index('speaker');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_transcripts');
    }
};
