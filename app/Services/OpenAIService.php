<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $defaultModel;

    public function __construct()
    {
        $this->apiKey = Setting::getValue('openai_api_key', config('openai.api_key')) ?? '';
        $this->baseUrl = config('openai.base_url', 'https://api.openai.com/v1');
        $this->defaultModel = Setting::getValue('openai_model', config('openai.default_model')) ?? 'gpt-4o-mini';
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function transcribe(string $audioUrl): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenAI API key not configured');

            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
            ])
                ->withoutVerifying()
                ->timeout(120)
                ->attach(
                    'file',
                    file_get_contents($audioUrl),
                    'audio.wav',
                    ['Content-Type' => 'audio/wav']
                )
                ->post($this->baseUrl.'/audio/transcriptions', [
                    'model' => 'whisper-1',
                    'response_format' => 'verbose_json',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'id' => uniqid('whisper_'),
                    'text' => $data['text'] ?? '',
                    'words' => $data['words'] ?? null,
                    'language' => $data['language'] ?? null,
                    'duration' => $data['duration'] ?? null,
                ];
            }

            Log::error('OpenAI Whisper transcription error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI Whisper transcription exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function chat(array $messages, ?string $model = null): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenAI API key not configured');

            return null;
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(60)
                ->post($this->baseUrl.'/chat/completions', [
                    'model' => $model ?? $this->defaultModel,
                    'messages' => $messages,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('OpenAI chat error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI chat exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function streamChat(array $messages, callable $onToken, ?string $model = null): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenAI API key not configured');

            return null;
        }

        try {
            $fullContent = '';

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(60)
                ->post($this->baseUrl.'/chat/completions', [
                    'model' => $model ?? $this->defaultModel,
                    'messages' => $messages,
                    'stream' => true,
                ]);

            if (! $response->successful()) {
                Log::error('OpenAI stream error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $stream = $response->getBody();

            while (! $stream->eof()) {
                $line = $stream->read(4096);

                if (strpos($line, 'data: ') === 0) {
                    $data = substr($line, 6);

                    if ($data === '[DONE]') {
                        break;
                    }

                    $json = json_decode($data, true);

                    if (isset($json['choices'][0]['delta']['content'])) {
                        $token = $json['choices'][0]['delta']['content'];
                        $fullContent .= $token;
                        $onToken($token);
                    }
                }
            }

            return $fullContent;
        } catch (\Exception $e) {
            Log::error('OpenAI stream exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function transcribeFromContent(string $content, string $filename = 'audio.wav'): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenAI API key not configured');

            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
            ])
                ->withoutVerifying()
                ->timeout(120)
                ->attach(
                    'file',
                    $content,
                    $filename,
                    ['Content-Type' => 'audio/wav']
                )
                ->post($this->baseUrl.'/audio/transcriptions', [
                    'model' => 'whisper-1',
                    'response_format' => 'verbose_json',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'id' => uniqid('whisper_'),
                    'text' => $data['text'] ?? '',
                    'words' => $data['words'] ?? null,
                    'language' => $data['language'] ?? null,
                    'duration' => $data['duration'] ?? null,
                ];
            }

            Log::error('OpenAI Whisper transcription error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI Whisper transcription exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function summarize(string $text, ?string $model = null): ?string
    {
        $result = $this->chat([
            ['role' => 'system', 'content' => 'You are a professional call summary generator. Create concise summaries of helpline calls.'],
            ['role' => 'user', 'content' => "Summarize this call transcript:\n\n{$text}"],
        ], $model ?? $this->defaultModel);

        if (! $result) {
            return null;
        }

        return $result['choices'][0]['message']['content'] ?? null;
    }

    public function analyzeCall(string $transcript, array $rubric): ?array
    {
        $criteriaList = '';
        foreach ($rubric as $id => $criterion) {
            $criteriaList .= "{$id}: {$criterion['name']}\n";
        }

        $systemPrompt = <<<'EOT'
You are a QA analyst for a substance abuse helpline. Evaluate call transcripts against the provided rubric criteria.
Return ONLY the evaluation in the exact format specified, nothing else.
EOT;

        $userPrompt = <<<EOT
Transcript to evaluate:
---
{$transcript}
---

Evaluate each criterion and return results in this exact format (one per line):
CRITERION_ID|PASS|Reason for pass
CRITERION_ID|FAIL|Reason for failure

Criteria to evaluate:
{$criteriaList}

Return ONLY the evaluation lines, no other text.
EOT;

        $result = $this->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ]);

        if (! $result) {
            return null;
        }

        return $this->parseRubricResults($result['choices'][0]['message']['content'] ?? '', $rubric);
    }

    protected function parseRubricResults(string $content, array $criteria): array
    {
        $results = [];
        $lines = explode("\n", trim($content));

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = explode('|', $line);
            if (count($parts) < 3) {
                continue;
            }

            $id = trim($parts[0]);
            $status = strtoupper(trim($parts[1]));
            $details = trim($parts[2] ?? '');

            if (isset($criteria[$id])) {
                $results[$id] = [
                    'pass' => ($status === 'PASS'),
                    'details' => $details,
                ];
            }
        }

        return $results;
    }
}
