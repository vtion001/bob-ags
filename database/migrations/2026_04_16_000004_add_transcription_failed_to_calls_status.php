<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so we need to recreate the table
            // Step 1: Create new table
            Schema::create('calls_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('ctm_call_id')->unique();
                $table->string('ctm_sid')->nullable();
                $table->string('tracking_number', 20)->nullable();
                $table->string('tracking_label')->nullable();
                $table->string('caller_number', 20)->nullable();
                $table->string('caller_city', 100)->nullable();
                $table->string('caller_state', 50)->nullable();
                $table->string('direction', 20)->default('inbound');
                $table->string('source', 100)->nullable();
                $table->string('agent_name', 100)->nullable();
                $table->string('agent_id', 50)->nullable();
                $table->string('recording_url')->nullable();
                $table->string('local_recording_path')->nullable();
                $table->boolean('transferred')->default(false);
                $table->string('transcript_url')->nullable();
                $table->text('transcript_text')->nullable();
                $table->json('transcript_json')->nullable();
                $table->string('transcript_id')->nullable();
                $table->string('status', 50)->default('pending');
                $table->timestamp('call_datetime');
                $table->integer('duration')->default(0);
                $table->integer('talk_time')->nullable();
                $table->timestamps();
                $table->index(['status', 'call_datetime']);
                $table->index(['caller_number', 'call_datetime']);
            });

            // Step 2: Copy data from old table to new table
            $columns = [
                'id', 'user_id', 'ctm_call_id', 'ctm_sid', 'tracking_number',
                'tracking_label', 'caller_number', 'caller_city', 'caller_state',
                'direction', 'source', 'agent_name', 'agent_id', 'recording_url',
                'local_recording_path', 'transferred', 'transcript_url', 'transcript_text',
                'transcript_json', 'transcript_id', 'call_datetime', 'duration',
                'talk_time', 'created_at', 'updated_at',
            ];

            $allColumns = DB::getSchemaBuilder()->getColumnListing('calls');

            // Get all data from old table
            $allData = DB::table('calls')->get();

            foreach ($allData as $row) {
                $insertData = [];
                foreach ($columns as $col) {
                    if (in_array($col, $allColumns)) {
                        $insertData[$col] = $row->$col ?? null;
                    }
                }
                // Ensure status is valid
                $insertData['status'] = in_array($row->status ?? 'pending', [
                    'pending', 'transcribed', 'analyzing', 'analyzed', 'completed', 'transcription_failed',
                ]) ? ($row->status ?? 'pending') : 'pending';

                DB::table('calls_new')->insert($insertData);
            }

            // Step 3: Drop old table and rename new table
            Schema::drop('calls');
            Schema::rename('calls_new', 'calls');
        } else {
            Schema::table('calls', function (Blueprint $table) {
                $table->string('status', 50)->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            // Recreate with original schema
            Schema::create('calls_old', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('ctm_call_id')->unique();
                $table->string('tracking_number', 20)->nullable();
                $table->string('tracking_label')->nullable();
                $table->string('caller_number', 20)->nullable();
                $table->string('caller_city', 100)->nullable();
                $table->string('caller_state', 50)->nullable();
                $table->string('direction', 20)->default('inbound');
                $table->string('source', 100)->nullable();
                $table->string('agent_name', 100)->nullable();
                $table->string('agent_id', 50)->nullable();
                $table->string('recording_url')->nullable();
                $table->string('transcript_url')->nullable();
                $table->text('transcript_text')->nullable();
                $table->json('transcript_json')->nullable();
                $table->string('transcript_id')->nullable();
                $table->string('status', 50)->default('pending');
                $table->timestamp('call_datetime');
                $table->integer('duration')->default(0);
                $table->timestamps();
                $table->index(['status', 'call_datetime']);
                $table->index(['caller_number', 'call_datetime']);
            });

            $allData = DB::table('calls')->get();
            $columns = ['id', 'user_id', 'ctm_call_id', 'tracking_number', 'tracking_label',
                'caller_number', 'caller_city', 'caller_state', 'direction', 'source',
                'agent_name', 'agent_id', 'recording_url', 'transcript_url', 'transcript_text',
                'transcript_json', 'transcript_id', 'call_datetime', 'duration', 'created_at', 'updated_at'];

            foreach ($allData as $row) {
                $insertData = [];
                foreach ($columns as $col) {
                    if (isset($row->$col)) {
                        $insertData[$col] = $row->$col;
                    }
                }
                $insertData['status'] = in_array($row->status ?? 'pending', [
                    'pending', 'transcribed', 'analyzing', 'analyzed', 'completed',
                ]) ? ($row->status ?? 'pending') : 'pending';

                DB::table('calls_old')->insert($insertData);
            }

            Schema::drop('calls');
            Schema::rename('calls_old', 'calls');
        } else {
            Schema::table('calls', function (Blueprint $table) {
                $table->enum('status', [
                    'pending',
                    'transcribed',
                    'analyzing',
                    'analyzed',
                    'completed',
                ])->default('pending')->change();
            });
        }
    }
};
