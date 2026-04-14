<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_base_id')->constrained()->cascadeOnDelete();
            $table->text('chunk');
            $table->integer('chunk_index')->default(0);
            $table->timestamps();

            $table->index('knowledge_base_id');
            $table->index('chunk_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_entries');
    }
};
