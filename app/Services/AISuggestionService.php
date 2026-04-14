<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AISuggestionService
{
    protected string $provider;
    protected KnowledgeBaseService $kbService;

    public function __construct(KnowledgeBaseService $kbService)
    {
        $this->provider = Setting::getValue('ai_provider', 'openai');
        $this->kbService = $kbService;
    }

    protected function getService()
    {
        return match($this->provider) {
            'openai' => app(OpenAIService::class),
            'anthropic' => app(AnthropicService::class),
            'openrouter' => app(OpenRouterService::class),
            default => app(OpenAIService::class),
        };
    }

    public function chat(array $messages, ?string $model = null): ?array
    {
        return $this->getService()->chat($messages, $model);
    }

    public function getWhatToSay(array $context): string
    {
        $transcript = $context['transcript'] ?? '';
        $kbContext = $this->kbService->getContextForAI($transcript, 3);

        $systemPrompt = <<<EOT
You are an AI assistant for a substance abuse helpline agent. Your role is to help agents provide compassionate, professional, and helpful responses to callers.
Key principles:
- Be empathetic and non-judgmental
- Use active listening techniques
- Follow proper protocols for crisis situations
- Never provide medical diagnoses or specific medical advice
- Always prioritize caller safety
- Keep responses concise and actionable (1-2 sentences)
EOT;

        $userPrompt = <<<EOT
Based on the conversation transcript and knowledge base context, suggest what the agent should say next.

Transcript:
{$transcript}

Knowledge Base Context:
{$kbContext}

Provide a single, concise suggestion for what the agent should say next. The suggestion should:
1. Be empathetic and supportive
2. Be appropriate for a substance abuse helpline context
3. Be 1-2 sentences maximum
4. Focus on the caller's needs

If the conversation is just starting, suggest a warm greeting and opening question.
If the caller is expressing distress, suggest a calming, supportive response.
If the caller is providing information, suggest a follow-up question or acknowledgment.

Response format: Just the suggestion text, nothing else.
EOT;

        $result = $this->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ]);

        if (!$result) {
            return 'I hear you. Can you tell me more about what you\'re going through?';
        }

        $content = $result['choices'][0]['message']['content'] ?? '';
        return trim($content) ?: 'I understand. Can you tell me more about that?';
    }

    public function getFollowUpQuestions(array $context): array
    {
        $transcript = $context['transcript'] ?? '';
        $kbContext = $this->kbService->getContextForAI($transcript, 3);

        $systemPrompt = "You are an AI assistant helping a substance abuse helpline agent ask relevant follow-up questions. Generate exactly 3 follow-up questions that would help the agent better understand the caller's situation.";

        $userPrompt = <<<EOT
Based on this conversation transcript, suggest 3 follow-up questions the agent should ask.

Transcript:
{$transcript}

Knowledge Base:
{$kbContext}

Generate exactly 3 follow-up questions. Each question should:
1. Be open-ended (not yes/no)
2. Be compassionate and non-judgmental
3. Help gather important information for qualification
4. Be relevant to substance abuse helpline context

Return in this exact format (one question per line):
1. [question]
2. [question]
3. [question]
EOT;

        $result = $this->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ]);

        if (!$result) {
            return [
                'How long have you been feeling this way?',
                'Can you tell me more about what brought you here today?',
                'Is there anything specific you\'d like to discuss?',
            ];
        }

        $content = $result['choices'][0]['message']['content'] ?? '';
        
        $questions = [];
        preg_match_all('/^\d+\.\s*(.+)$/m', $content, $matches);
        
        if (!empty($matches[1])) {
            $questions = array_map('trim', $matches[1]);
        }

        while (count($questions) < 3) {
            $questions[] = 'Can you tell me more about that?';
        }

        return array_slice($questions, 0, 3);
    }

    public function getSuggestedResources(array $context): array
    {
        $transcript = $context['transcript'] ?? '';

        $resources = [
            [
                'name' => '988 Suicide & Crisis Lifeline',
                'description' => 'Call or text 988 for immediate support',
                'action' => 'Press 1 for SAMHSA helpline',
                'type' => 'crisis',
            ],
            [
                'name' => 'Crisis Text Line',
                'description' => 'Text HOME to 741741',
                'action' => 'Free 24/7 crisis support via text',
                'type' => 'crisis',
            ],
            [
                'name' => 'SAMHSA National Helpline',
                'description' => '1-800-662-4357',
                'action' => 'Free, confidential treatment referral',
                'type' => 'treatment',
            ],
        ];

        $keywords = ['overdose', 'relapse', 'suicide', 'self-harm', 'crisis', 'emergency', 'danger'];
        $hasCrisis = false;
        
        foreach ($keywords as $keyword) {
            if (stripos($transcript, $keyword) !== false) {
                $hasCrisis = true;
                break;
            }
        }

        if ($hasCrisis) {
            array_unshift($resources, [
                'name' => 'Emergency Services',
                'description' => 'Call 911 if there is immediate danger',
                'action' => 'Ensure caller safety first',
                'type' => 'emergency',
            ]);
        }

        return $resources;
    }

    public function answerQuestion(string $question, array $context): string
    {
        $transcript = $context['transcript'] ?? '';
        $kbContext = $this->kbService->getContextForAI($transcript . ' ' . $question, 5);

        $systemPrompt = "You are an AI assistant for a substance abuse helpline agent. Answer questions based on the conversation context and knowledge base provided. Be helpful, accurate, and concise.";

        $userPrompt = <<<EOT
Question: {$question}

Conversation Context:
{$transcript}

Knowledge Base:
{$kbContext}

Answer the question based on the context. If the answer requires information not in the context, say so and suggest where the agent might find that information.

Keep your answer concise and actionable (2-3 sentences maximum).
EOT;

        $result = $this->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ]);

        if (!$result) {
            return 'I\'m sorry, I couldn\'t process that question. Please try again or consult the knowledge base.';
        }

        return trim($result['choices'][0]['message']['content'] ?? 'I understand. Let me help you with that.');
    }
}
