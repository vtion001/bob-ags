<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qa_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained()->cascadeOnDelete();
            $table->foreignId('analyst_id')->constrained('users');
            $table->decimal('total_score', 5, 2)->default(0);
            $table->boolean('ztp_failed')->default(false);
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->default('neutral');
            $table->enum('disposition', ['auto-fail', 'unqualified', 'qualified', 'warm', 'refer', 'do-not-refer'])->nullable();
            $table->json('criteria_scores');
            $table->json('rubric_breakdown');
            $table->json('ztp_violations')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['analyst_id', 'created_at']);
            $table->index(['ztp_failed', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_logs');
    }
};
