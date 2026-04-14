<?php

namespace App\Jobs;

use App\Events\AITokensGenerated;
use App\Models\LiveMonitoring;
use App\Services\AISuggestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAIAsync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $sessionId;
    public string $type; // 'suggestion', 'chat', 'follow_up'
    public string $question;

    public function __construct(string $sessionId, string $type = 'suggestion', string $question = '')
    {
        $this->sessionId = $sessionId;
        $this->type = $type;
        $this->question = $question;
    }

    public function handle(AISuggestionService $ai): void
    {
        $session = LiveMonitoring::where('session_id', $this->sessionId)->first();
        
        if (!$session) {
            Log::warning('ProcessAIAsync: Session not found', ['session_id' => $this->sessionId]);
            return;
        }

        try {
            if ($this->type === 'suggestion') {
                $this->processSuggestions($session, $ai);
            } elseif ($this->type === 'chat') {
                $this->processChat($session, $ai);
            } elseif ($this->type === 'follow_up') {
                $this->processFollowUp($session, $ai);
            }
        } catch (\Exception $e) {
            Log::error('ProcessAIAsync failed', [
                'session_id' => $this->sessionId,
                'error' => $e->getMessage(),
            ]);
            
            event(new AITokensGenerated(
                $this->sessionId,
                $this->type,
                ['error' => 'Failed to process request'],
                true
            ));
        }
    }

    protected function processSuggestions(LiveMonitoring $session, AISuggestionService $ai): void
    {
        $context = [
            'transcript' => $session->transcript_text ?? '',
            'caller_number' => $session->caller_number,
        ];

        $fullResponse = '';
        
        $streamCallback = function(string $token) use (&$fullResponse) {
            $fullResponse .= $token;
            $words = explode(' ', trim($fullResponse));
            if (count($words) >= 2) {
                event(new AITokensGenerated(
                    $this->sessionId,
                    'suggestion',
                    $words,
                    false
                ));
            }
        };

        $suggestion = $ai->getWhatToSayStreaming($context, $streamCallback);
        
        if (empty($fullResponse) && $suggestion) {
            $fullResponse = $suggestion;
        }

        $session->update([
            'active_suggestions' => array_merge($session->active_suggestions ?? [], [
                'what_to_say' => $fullResponse,
                'generated_at' => now()->toIso8601String(),
            ]),
        ]);

        event(new AITokensGenerated(
            $this->sessionId,
            'suggestion',
            explode(' ', trim($fullResponse)),
            true
        ));
    }

    protected function processChat(LiveMonitoring $session, AISuggestionService $ai): void
    {
        $context = [
            'transcript' => $session->transcript_text ?? '',
            'caller_number' => $session->caller_number,
        ];

        $fullResponse = '';
        
        $streamCallback = function(string $token) use (&$fullResponse) {
            $fullResponse .= $token;
            $words = explode(' ', trim($fullResponse));
            if (count($words) >= 2) {
                event(new AITokensGenerated(
                    $this->sessionId,
                    'chat',
                    $words,
                    false
                ));
            }
        };

        $answer = $ai->answerQuestionStreaming($this->question, $context, $streamCallback);
        
        if (empty($fullResponse) && $answer) {
            $fullResponse = $answer;
        }

        event(new AITokensGenerated(
            $this->sessionId,
            'chat',
            explode(' ', trim($fullResponse)),
            true
        ));
    }

    protected function processFollowUp(LiveMonitoring $session, AISuggestionService $ai): void
    {
        $context = [
            'transcript' => $session->transcript_text ?? '',
            'caller_number' => $session->caller_number,
        ];

        $questions = $ai->getFollowUpQuestionsStreaming($context);
        
        $allWords = [];
        foreach ($questions as $question) {
            $allWords = array_merge($allWords, explode(' ', $question));
        }

        event(new AITokensGenerated(
            $this->sessionId,
            'follow_up',
            $allWords,
            true
        ));
    }
}
