<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssemblyAIService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $streamingUrl;

    public function __construct()
    {
        $this->apiKey = config('assemblyai.api_key');
        $this->baseUrl = config('assemblyai.base_url');
        $this->streamingUrl = config('assemblyai.streaming_url');
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function uploadFile(string $filePath): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])
                ->withoutVerifying()
                ->timeout(60)
                ->withBody(fopen($filePath, 'r'), 'audio/mpeg')
                ->post($this->baseUrl.'/v2/upload');

            if ($response->successful()) {
                return $response->json()['upload_url'];
            }

            Log::error('AssemblyAI upload error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('AssemblyAI upload exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function transcribe(string $audioUrl, array $options = []): ?array
    {
        try {
            $data = array_merge([
                'audio_url' => $audioUrl,
                'speech_model' => 'universal-3-pro',
                'speaker_labels' => true,
                'language_detection' => true,
            ], $options);

            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->post($this->baseUrl.'/v2/transcript', $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AssemblyAI transcribe error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('AssemblyAI transcribe exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function getTranscript(string $transcriptId): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get($this->baseUrl.'/v2/transcript/'.$transcriptId);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AssemblyAI getTranscript error', [
                'transcript_id' => $transcriptId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('AssemblyAI getTranscript exception', [
                'transcript_id' => $transcriptId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function pollTranscript(string $transcriptId, int $maxAttempts = 60, int $delaySeconds = 3): ?array
    {
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $transcript = $this->getTranscript($transcriptId);

            if (! $transcript) {
                return null;
            }

            $status = $transcript['status'] ?? 'unknown';

            if ($status === 'completed') {
                return $transcript;
            }

            if ($status === 'error') {
                Log::error('AssemblyAI transcript error', [
                    'transcript_id' => $transcriptId,
                    'error' => $transcript['error'] ?? 'Unknown error',
                ]);

                return null;
            }

            sleep($delaySeconds);
            $attempts++;
        }

        Log::warning('AssemblyAI poll timeout', ['transcript_id' => $transcriptId]);

        return null;
    }

    public function getSubtitles(string $transcriptId, string $format = 'srt'): ?string
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get($this->baseUrl.'/v2/transcript/'.$transcriptId.'/subtitles', [
                    'format' => $format,
                ]);

            if ($response->successful()) {
                return $response->body();
            }

            Log::error('AssemblyAI getSubtitles error', [
                'transcript_id' => $transcriptId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('AssemblyAI getSubtitles exception', [
                'transcript_id' => $transcriptId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getWords(string $transcriptId): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get($this->baseUrl.'/v2/transcript/'.$transcriptId.'/words');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AssemblyAI getWords error', [
                'transcript_id' => $transcriptId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('AssemblyAI getWords exception', [
                'transcript_id' => $transcriptId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
