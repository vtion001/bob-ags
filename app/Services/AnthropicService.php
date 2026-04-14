<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $apiVersion;
    protected string $defaultModel;

    public function __construct()
    {
        $this->apiKey = Setting::getValue('anthropic_api_key', config('anthropic.api_key')) ?? '';
        $this->baseUrl = config('anthropic.base_url', 'https://api.anthropic.com/v1');
        $this->apiVersion = config('anthropic.api_version', '2023-06-01');
        $this->defaultModel = Setting::getValue('anthropic_model', config('anthropic.default_model')) ?? 'claude-3-5-sonnet-20241022';
    }

    protected function getHeaders(): array
    {
        return [
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
            'content-type' => 'application/json',
        ];
    }

    public function chat(array $messages, ?string $model = null): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('Anthropic API key not configured');
            return null;
        }

        try {
            $systemMessage = '';
            $filteredMessages = [];
            
            foreach ($messages as $msg) {
                if ($msg['role'] === 'system') {
                    $systemMessage = $msg['content'];
                } else {
                    $filteredMessages[] = $msg;
                }
            }

            $requestBody = [
                'model' => $model ?? $this->defaultModel,
                'messages' => $filteredMessages,
                'max_tokens' => 1024,
            ];

            if (!empty($systemMessage)) {
                $requestBody['system'] = $systemMessage;
            }

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(60)
                ->post($this->baseUrl . '/messages', $requestBody);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'choices' => [
                        [
                            'message' => [
                                'content' => $data['content'][0]['text'] ?? '',
                            ]
                        ]
                    ]
                ];
            }

            Log::error('Anthropic chat error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Anthropic chat exception', ['error' => $e->getMessage()]);
            return null;
        }
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
            Log::warning('Anthropic API key not configured');
            return null;
        }

        try {
            $systemMessage = '';
            $filteredMessages = [];
            
            foreach ($messages as $msg) {
                if ($msg['role'] === 'system') {
                    $systemMessage = $msg['content'];
                } else {
                    $filteredMessages[] = $msg;
                }
            }

            $requestBody = [
                'model' => $model ?? $this->defaultModel,
                'messages' => $filteredMessages,
                'max_tokens' => 1024,
                'stream' => true,
            ];

            if (!empty($systemMessage)) {
                $requestBody['system'] = $systemMessage;
            }

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(60)
                ->post($this->baseUrl . '/messages', $requestBody);

            if (!$response->successful()) {
                Log::error('Anthropic stream error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $fullContent = '';
            $stream = $response->getBody();
            
            while (!$stream->eof()) {
                $line = $stream->read(4096);
                
                if (strpos($line, 'data: ') === 0) {
                    $data = substr($line, 6);
                    
                    if ($data === '[DONE]') {
                        break;
                    }
                    
                    $json = json_decode($data, true);
                    
                    if (isset($json['type']) && $json['type'] === 'content_block_delta') {
                        if (isset($json['delta']['text'])) {
                            $token = $json['delta']['text'];
                            $fullContent .= $token;
                            $onToken($token);
                        }
                    }
                }
            }

            return $fullContent;
        } catch (\Exception $e) {
            Log::error('Anthropic stream exception', ['error' => $e->getMessage()]);
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
}
