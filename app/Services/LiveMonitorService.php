<?php

namespace App\Services;

use App\Models\LiveMonitoring;
use App\Models\LiveTranscript;
use Illuminate\Support\Facades\Log;

class LiveMonitorService
{
    protected AISuggestionService $aiService;
    protected ZTPAlertService $ztpService;

    public function __construct(AISuggestionService $aiService, ZTPAlertService $ztpService)
    {
        $this->aiService = $aiService;
        $this->ztpService = $ztpService;
    }

    public function startSession(?int $userId, ?string $ctmCallId, ?string $callerNumber, ?string $agentName): LiveMonitoring
    {
        $session = LiveMonitoring::create([
            'session_id' => LiveMonitoring::generateSessionId(),
            'ctm_call_id' => $ctmCallId,
            'user_id' => $userId,
            'agent_name' => $agentName,
            'caller_number' => $callerNumber,
            'status' => 'active',
            'started_at' => now(),
        ]);

        Log::info('Live monitoring session started', ['session_id' => $session->session_id]);

        return $session;
    }

    public function endSession(LiveMonitoring $session): void
    {
        $session->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        Log::info('Live monitoring session ended', ['session_id' => $session->session_id]);
    }

    public function pauseSession(LiveMonitoring $session): void
    {
        $session->update(['status' => 'paused']);
    }

    public function resumeSession(LiveMonitoring $session): void
    {
        $session->update(['status' => 'active']);
    }

    public function processTranscriptChunk(
        LiveMonitoring $session,
        string $text,
        array $words = [],
        string $speaker = 'unknown',
        ?float $startTime = null,
        ?float $endTime = null
    ): void {
        $transcript = $session->appendTranscript($text, $speaker, $startTime, $endTime);

        $session->refresh();

        $ztpAlert = $this->ztpService->checkForZTPViolation($text, $session->transcript_text ?? '');
        if ($ztpAlert) {
            $session->addZtpAlert($ztpAlert);
            Log::warning('ZTP violation detected', [
                'session_id' => $session->session_id,
                'alert' => $ztpAlert,
            ]);
        }

        $suggestions = $this->generateSuggestions($session);
        $session->updateSuggestions($suggestions);

        Log::debug('Transcript chunk processed', [
            'session_id' => $session->session_id,
            'text_length' => strlen($text),
            'speaker' => $speaker,
        ]);
    }

    public function generateSuggestions(LiveMonitoring $session): array
    {
        try {
            $context = [
                'transcript' => $session->transcript_text ?? '',
                'caller_number' => $session->caller_number,
            ];

            return [
                'what_to_say' => $this->aiService->getWhatToSay($context),
                'follow_up_questions' => $this->aiService->getFollowUpQuestions($context),
                'suggested_resources' => $this->aiService->getSuggestedResources($context),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate suggestions', [
                'session_id' => $session->session_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'what_to_say' => 'I understand. Can you tell me more about that?',
                'follow_up_questions' => [],
                'suggested_resources' => [],
            ];
        }
    }

    public function answerQuestion(LiveMonitoring $session, string $question): string
    {
        try {
            $context = [
                'transcript' => $session->transcript_text ?? '',
                'caller_number' => $session->caller_number,
            ];

            return $this->aiService->answerQuestion($question, $context);
        } catch (\Exception $e) {
            Log::error('Failed to answer question', [
                'session_id' => $session->session_id,
                'error' => $e->getMessage(),
            ]);

            return 'I apologize, I could not process that request. Please try again.';
        }
    }

    public function getSessionStatus(string $sessionId): ?array
    {
        $session = LiveMonitoring::where('session_id', $sessionId)->first();

        if (!$session) {
            return null;
        }

        return [
            'session_id' => $session->session_id,
            'status' => $session->status,
            'transcript_text' => $session->transcript_text,
            'active_suggestions' => $session->active_suggestions,
            'ztp_alerts' => $session->ztp_alerts,
            'started_at' => $session->started_at?->toIso8601String(),
            'ended_at' => $session->ended_at?->toIso8601String(),
        ];
    }
}
