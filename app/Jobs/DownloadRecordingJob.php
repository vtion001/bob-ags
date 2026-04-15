<?php

namespace App\Jobs;

use App\Models\Call;
use App\Services\CTMService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadRecordingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    protected int $callId;

    public function __construct(int $callId)
    {
        $this->callId = $callId;
    }

    public function handle(CTMService $ctm): void
    {
        $call = Call::find($this->callId);

        if (! $call) {
            Log::error('DownloadRecordingJob: Call not found', ['call_id' => $this->callId]);

            return;
        }

        if (empty($call->recording_url)) {
            Log::debug('DownloadRecordingJob: No recording URL', ['call_id' => $this->callId]);

            return;
        }

        // Check if recording already exists locally
        $localPath = $this->getLocalPath($call);
        if (Storage::disk('recordings')->exists($localPath)) {
            Log::debug('DownloadRecordingJob: Recording already exists', [
                'call_id' => $this->callId,
                'path' => $localPath,
            ]);

            return;
        }

        Log::info('DownloadRecordingJob: Downloading recording', [
            'call_id' => $this->callId,
            'recording_url' => substr($call->recording_url, 0, 100),
        ]);

        try {
            $content = Http::withHeaders([
                'Authorization' => $ctm->getAuthHeader(),
            ])
                ->withoutVerifying()
                ->get($call->recording_url)
                ->body();

            if (empty($content)) {
                Log::error('DownloadRecordingJob: Empty response', ['call_id' => $this->callId]);

                return;
            }

            // Ensure directory exists
            $directory = dirname($localPath);
            if (! Storage::disk('recordings')->exists($directory)) {
                Storage::disk('recordings')->makeDirectory($directory);
            }

            // Save file
            Storage::disk('recordings')->put($localPath, $content);

            // Update call with local path
            $call->update(['local_recording_path' => $localPath]);

            Log::info('DownloadRecordingJob: Recording saved', [
                'call_id' => $this->callId,
                'path' => $localPath,
                'size' => strlen($content),
            ]);
        } catch (\Exception $e) {
            Log::error('DownloadRecordingJob: Download failed', [
                'call_id' => $this->callId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function getLocalPath(Call $call): string
    {
        $date = $call->call_datetime ? $call->call_datetime->format('Y/m') : date('Y/m');

        return "{$date}/{$call->ctm_call_id}.mp3";
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('DownloadRecordingJob: Job failed', [
            'call_id' => $this->callId,
            'error' => $exception->getMessage(),
        ]);
    }
}
