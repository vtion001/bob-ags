<?php

namespace App\Jobs;

use App\Models\Call;
use App\Services\CTMService;
use App\Services\OpenAIService;
use App\Services\QAAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    public function handle(OpenAIService $openAI, QAAnalysisService $qa, CTMService $ctm): void
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

        $audioContent = $this->downloadRecording($call, $ctm);

        if (! $audioContent) {
            Log::error('TranscribeCallJob: Failed to download recording', [
                'call_id' => $this->callId,
                'recording_url' => $this->recordingUrl,
            ]);

            $call->update(['status' => 'transcription_failed']);

            return;
        }

        $transcript = $openAI->transcribeFromContent($audioContent, 'audio.wav');

        if (! $transcript || empty($transcript['text'])) {
            Log::error('TranscribeCallJob: OpenAI Whisper transcription failed', [
                'call_id' => $this->callId,
                'response' => $transcript,
            ]);

            $call->update(['status' => 'transcription_failed']);

            return;
        }

        $transcriptText = $transcript['text'] ?? '';
        $transcriptJson = $transcript['words'] ?? null;

        $isTransferred = $qa->detectTransfer($transcriptText);

        Log::info('TranscribeCallJob: Transcription completed', [
            'call_id' => $this->callId,
            'transcript_length' => strlen($transcriptText),
            'transferred' => $isTransferred,
        ]);

        $call->update([
            'transcript_text' => $transcriptText,
            'transcript_json' => $transcriptJson,
            'transcript_id' => $transcript['id'],
            'transferred' => $isTransferred,
            'status' => 'transcribed',
        ]);
    }

    protected function downloadRecording(Call $call, CTMService $ctm): ?string
    {
        $recordingUrl = $this->recordingUrl;

        if (empty($recordingUrl)) {
            return null;
        }

        $content = null;

        if ($call->local_recording_path && Storage::disk('recordings')->exists($call->local_recording_path)) {
            $content = Storage::disk('recordings')->get($call->local_recording_path);
            Log::info('TranscribeCallJob: Using local recording', [
                'call_id' => $this->callId,
                'size' => strlen($content),
            ]);
        } else {
            Log::info('TranscribeCallJob: Downloading from CTM', [
                'call_id' => $this->callId,
                'url' => substr($recordingUrl, 0, 100),
            ]);

            try {
                $response = Http::withHeaders([
                    'Authorization' => $ctm->getAuthHeader(),
                ])
                    ->withoutVerifying()
                    ->timeout(60)
                    ->get($recordingUrl);

                if ($response->successful()) {
                    $content = $response->body();
                    Log::info('TranscribeCallJob: Downloaded from CTM', [
                        'call_id' => $this->callId,
                        'size' => strlen($content),
                    ]);
                } else {
                    Log::error('TranscribeCallJob: CTM download failed', [
                        'call_id' => $this->callId,
                        'status' => $response->status(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('TranscribeCallJob: CTM download exception', [
                    'call_id' => $this->callId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $content;
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
