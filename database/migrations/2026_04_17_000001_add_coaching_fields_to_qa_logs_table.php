<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qa_logs', function (Blueprint $table) {
            $table->text('coaching_insights')->nullable()->after('notes');
            $table->text('recommendations')->nullable()->after('coaching_insights');
        });
    }

    public function down(): void
    {
        Schema::table('qa_logs', function (Blueprint $table) {
            $table->dropColumn(['coaching_insights', 'recommendations']);
        });
    }
};
