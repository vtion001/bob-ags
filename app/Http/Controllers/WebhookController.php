<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\LiveMonitoring;
use App\Models\User;
use App\Services\LiveMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected LiveMonitorService $monitorService;

    public function __construct(LiveMonitorService $monitorService)
    {
        $this->monitorService = $monitorService;
    }

    public function handleCtm(Request $request)
    {
        try {
            $event = $request->input('event');
            $data = $request->input('data', $request->input('call', []));

            Log::info('CTM Webhook received', ['event' => $event, 'data' => $data]);

            return match($event) {
                'call.start' => $this->handleCallStart($data),
                'call.end' => $this->handleCallEnd($data),
                'call.complete' => $this->handleCallComplete($data),
                default => response()->json(['status' => 'ignored', 'message' => 'Event not handled']),
            };
        } catch (\Exception $e) {
            Log::error('CTM Webhook error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    protected function handleCallStart(array $data): \Illuminate\Http\JsonResponse
    {
        $ctmCallId = $data['id'] ?? null;
        
        if (!$ctmCallId) {
            return response()->json(['status' => 'error', 'message' => 'Missing call ID']);
        }

        $agentName = $data['agent_name'] ?? null;
        $callerNumber = $data['caller_number'] ?? null;

        $user = User::where('ctm_agent_id', $data['agent_id'] ?? null)->first();
        
        if (!$user) {
            Log::warning('No user found for CTM agent', ['agent_id' => $data['agent_id'] ?? null]);
        }

        $session = $this->monitorService->startSession(
            $user?->id,
            $ctmCallId,
            $callerNumber,
            $agentName
        );

        Log::info('Live monitoring session started via webhook', [
            'session_id' => $session->session_id,
            'ctm_call_id' => $ctmCallId,
        ]);

        return response()->json([
            'status' => 'ok',
            'session_id' => $session->session_id,
            'message' => 'Monitoring session started',
        ]);
    }

    protected function handleCallEnd(array $data): \Illuminate\Http\JsonResponse
    {
        $ctmCallId = $data['id'] ?? null;

        if (!$ctmCallId) {
            return response()->json(['status' => 'error', 'message' => 'Missing call ID']);
        }

        $session = LiveMonitoring::where('ctm_call_id', $ctmCallId)
            ->where('status', 'active')
            ->first();

        if ($session) {
            $this->monitorService->endSession($session);
            Log::info('Live monitoring session ended via webhook', ['session_id' => $session->session_id]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Session ended',
        ]);
    }

    protected function handleCallComplete(array $data): \Illuminate\Http\JsonResponse
    {
        $ctmCallId = $data['id'] ?? null;

        if (!$ctmCallId) {
            return response()->json(['status' => 'error', 'message' => 'Missing call ID']);
        }

        $call = Call::where('ctm_call_id', $ctmCallId)->first();

        if ($call) {
            $call->update([
                'recording_url' => $data['recording_url'] ?? $call->recording_url,
                'duration' => $data['duration'] ?? $call->duration,
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Call data updated',
        ]);
    }

    public function handleAssemblyAi(Request $request)
    {
        try {
            $event = $request->input('event');
            $transcriptId = $request->input('transcript_id');

            Log::info('AssemblyAI Webhook received', ['event' => $event, 'transcript_id' => $transcriptId]);

            if ($event === 'transcript' && isset($request->text)) {
                $sessionId = $request->input('session_id');

                if ($sessionId) {
                    $session = LiveMonitoring::where('session_id', $sessionId)->first();

                    if ($session) {
                        $this->monitorService->processTranscriptChunk(
                            $session,
                            $request->input('text'),
                            $request->input('words', []),
                            $request->input('speaker', 'unknown'),
                            $request->input('start_time'),
                            $request->input('end_time')
                        );
                    }
                }
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('AssemblyAI Webhook error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
