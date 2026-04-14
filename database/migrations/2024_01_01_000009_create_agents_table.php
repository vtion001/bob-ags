<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ctm_agent_id', 50)->unique();
            $table->string('ctm_agent_email', 255)->nullable();
            $table->string('ctm_agent_name', 255);
            $table->timestamps();
            
            $table->index('ctm_agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
