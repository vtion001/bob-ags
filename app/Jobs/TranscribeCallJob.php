<?php

namespace App\Jobs;

use App\Models\Call;
use App\Services\OpenAIService;
use App\Services\QAAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranscribeCallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    protected int $callId;

    protected string $recordingUrl;

    public function __construct(int $callId, string $recordingUrl)
    {
        $this->callId = $callId;
        $this->recordingUrl = $recordingUrl;
    }

    public function handle(OpenAIService $openAI, QAAnalysisService $qa): void
    {
        $call = Call::find($this->callId);

        if (! $call) {
            Log::error('TranscribeCallJob: Call not found', ['call_id' => $this->callId]);

            return;
        }

        Log::info('TranscribeCallJob: Starting transcription with OpenAI Whisper', [
            'call_id' => $this->callId,
            'recording_url' => $this->recordingUrl,
        ]);

        // Transcribe using OpenAI Whisper
        $transcript = $openAI->transcribe($this->recordingUrl);

        if (! $transcript || empty($transcript['text'])) {
            Log::error('TranscribeCallJob: OpenAI Whisper transcription failed', [
                'call_id' => $this->callId,
                'response' => $transcript,
            ]);

            $call->update(['status' => 'transcription_failed']);

            return;
        }

        // Extract transcript text
        $transcriptText = $transcript['text'] ?? '';
        $transcriptJson = $transcript['words'] ?? null;

        // Detect if call was transferred
        $isTransferred = $qa->detectTransfer($transcriptText);

        Log::info('TranscribeCallJob: Transcription completed', [
            'call_id' => $this->callId,
            'transcript_length' => strlen($transcriptText),
            'transferred' => $isTransferred,
        ]);

        // Update call with transcription results
        $call->update([
            'transcript_text' => $transcriptText,
            'transcript_json' => $transcriptJson,
            'transcript_id' => $transcript['id'],
            'transferred' => $isTransferred,
            'status' => 'transcribed',
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $call = Call::find($this->callId);

        if ($call) {
            $call->update(['status' => 'transcription_failed']);
        }

        Log::error('TranscribeCallJob: Job failed', [
            'call_id' => $this->callId,
            'error' => $exception->getMessage(),
        ]);
    }
}
