<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Services\CTMService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class RecordingController extends Controller
{
    protected CTMService $ctm;

    public function __construct(CTMService $ctm)
    {
        $this->ctm = $ctm;
    }

    public function show(string $callId)
    {
        $call = Call::where('ctm_call_id', $callId)->first();

        if (! $call) {
            abort(404, 'Call not found');
        }

        $recordingUrl = $call->recording_url;
        $localPath = $call->local_recording_path;

        if (! $recordingUrl && ! $localPath) {
            abort(404, 'No recording available for this call');
        }

        // First try to serve from local storage (faster)
        if ($localPath && Storage::disk('recordings')->exists($localPath)) {
            Log::info('RecordingController: Serving from local storage', [
                'call_id' => $callId,
                'path' => $localPath,
            ]);

            $content = Storage::disk('recordings')->get($localPath);
            $size = strlen($content);

            return Response::make($content, 200, [
                'Content-Type' => 'audio/mpeg',
                'Content-Length' => $size,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        // Fall back to downloading from CTM
        Log::info('RecordingController: Downloading from CTM', [
            'call_id' => $callId,
            'recording_url' => substr($recordingUrl, 0, 100),
        ]);

        try {
            $httpResponse = Http::withHeaders([
                'Authorization' => $this->ctm->getAuthHeader(),
            ])
                ->withoutVerifying()
                ->get($recordingUrl);

            if (! $httpResponse->successful()) {
                Log::error('RecordingController: Failed to fetch recording', [
                    'call_id' => $callId,
                    'status' => $httpResponse->status(),
                ]);
                abort(500, 'Failed to fetch recording from CTM');
            }

            $content = $httpResponse->body();
            $contentType = $httpResponse->header('Content-Type', 'audio/mpeg');
            $size = strlen($content);

            Log::info('RecordingController: Serving downloaded file', [
                'call_id' => $callId,
                'size' => $size,
                'content_type' => $contentType,
            ]);

            return Response::make($content, 200, [
                'Content-Type' => $contentType,
                'Content-Length' => $size,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        } catch (\Exception $e) {
            Log::error('RecordingController: Exception', [
                'call_id' => $callId,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Error fetching recording: '.$e->getMessage());
        }
    }

    public function download(string $callId)
    {
        $call = Call::where('ctm_call_id', $callId)->first();

        if (! $call) {
            abort(404, 'Call not found');
        }

        $recordingUrl = $call->recording_url;
        $localPath = $call->local_recording_path;

        if (! $recordingUrl && ! $localPath) {
            abort(404, 'No recording available for this call');
        }

        // First try local storage
        if ($localPath && Storage::disk('recordings')->exists($localPath)) {
            Log::info('RecordingController: Downloading from local storage', [
                'call_id' => $callId,
                'path' => $localPath,
            ]);

            $content = Storage::disk('recordings')->get($localPath);
            $filename = "recording_{$callId}.mp3";

            return Response::make($content, 200, [
                'Content-Type' => 'audio/mpeg',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Content-Length' => strlen($content),
            ]);
        }

        // Fall back to streaming from CTM
        try {
            $content = Http::withHeaders([
                'Authorization' => $this->ctm->getAuthHeader(),
            ])
                ->withoutVerifying()
                ->get($recordingUrl)
                ->body();

            $filename = "recording_{$callId}.mp3";

            return Response::make($content, 200, [
                'Content-Type' => 'audio/mpeg',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Content-Length' => strlen($content),
            ]);
        } catch (\Exception $e) {
            Log::error('RecordingController: Download exception', [
                'call_id' => $callId,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Error downloading recording');
        }
    }
}
