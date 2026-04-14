<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $defaultModel;

    public function __construct()
    {
        $this->apiKey = config('openrouter.api_key');
        $this->baseUrl = config('openrouter.base_url', 'https://openrouter.ai/api/v1');
        $this->defaultModel = config('openrouter.default_model', 'anthropic/claude-3-haiku');
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => config('app.url', 'http://localhost'),
            'X-Title' => config('app.name', 'BOB-AGS'),
        ];
    }

    public function chat(array $messages, ?string $model = null): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenRouter API key not configured');
            return null;
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(60)
                ->post($this->baseUrl . '/chat/completions', [
                    'model' => $model ?? $this->defaultModel,
                    'messages' => $messages,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('OpenRouter chat error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('OpenRouter chat exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function analyzeCall(string $transcript, array $rubric): ?array
    {
        $criteriaList = '';
        foreach ($rubric as $id => $criterion) {
            $criteriaList .= "{$id}: {$criterion['name']}\n";
        }

        $systemPrompt = <<<EOT
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

        if (!$result) {
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
            if (empty($line)) continue;

            $parts = explode('|', $line);
            if (count($parts) < 3) continue;

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

    public function summarize(string $text, ?string $model = null): ?string
    {
        $result = $this->chat([
            ['role' => 'system', 'content' => 'You are a professional call summary generator. Create concise summaries of helpline calls.'],
            ['role' => 'user', 'content' => "Summarize this call transcript:\n\n{$text}"],
        ], $model ?? $this->defaultModel);

        if (!$result) {
            return null;
        }

        return $result['choices'][0]['message']['content'] ?? null;
    }

    public function streamChat(array $messages, callable $onToken, ?string $model = null): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenRouter API key not configured');
            return null;
        }

        try {
            $fullContent = '';
            
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(60)
                ->post($this->baseUrl . '/chat/completions', [
                    'model' => $model ?? $this->defaultModel,
                    'messages' => $messages,
                    'stream' => true,
                ]);

            if (!$response->successful()) {
                Log::error('OpenRouter stream error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $stream = $response->getBody();
            
            while (!$stream->eof()) {
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
            Log::error('OpenRouter stream exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function extractKeyPhrases(string $text): ?array
    {
        $result = $this->chat([
            ['role' => 'system', 'content' => 'You are a professional call analyzer. Extract key phrases and topics from helpline calls. Return a JSON array of key phrases.'],
            ['role' => 'user', 'content' => "Extract key phrases from this transcript:\n\n{$text}"],
        ]);

        if (!$result) {
            return null;
        }

        $content = $result['choices'][0]['message']['content'] ?? '';
        $content = trim($content);
        
        if (str_starts_with($content, '```json')) {
            $content = substr($content, 7);
        }
        if (str_starts_with($content, '```')) {
            $content = substr($content, 3);
        }
        if (str_ends_with($content, '```')) {
            $content = substr($content, 0, -3);
        }

        $decoded = json_decode(trim($content), true);
        return is_array($decoded) ? $decoded : null;
    }

    public function getSentiment(string $text): ?array
    {
        $result = $this->chat([
            ['role' => 'system', 'content' => 'You are a professional call sentiment analyzer. Return a JSON object with sentiment analysis. Format: {"sentiment": "positive/neutral/negative", "confidence": 0.0-1.0, "summary": "brief explanation"}'],
            ['role' => 'user', 'content' => "Analyze the sentiment of this call:\n\n{$text}"],
        ]);

        if (!$result) {
            return null;
        }

        $content = $result['choices'][0]['message']['content'] ?? '';
        $content = trim($content);
        
        if (str_starts_with($content, '```json')) {
            $content = substr($content, 7);
        }
        if (str_starts_with($content, '```')) {
            $content = substr($content, 3);
        }
        if (str_ends_with($content, '```')) {
            $content = substr($content, 0, -3);
        }

        return json_decode(trim($content), true);
    }
}
