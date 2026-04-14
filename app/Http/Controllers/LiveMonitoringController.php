<?php

namespace App\Http\Controllers;

use App\Models\LiveMonitoring;
use App\Services\LiveMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LiveMonitoringController extends Controller
{
    protected LiveMonitorService $monitorService;

    public function __construct(LiveMonitorService $monitorService)
    {
        $this->monitorService = $monitorService;
    }

    public function index(Request $request): View
    {
        $activeSessions = LiveMonitoring::with('user')
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->get();

        $recentSessions = LiveMonitoring::with('user')
            ->where('user_id', Auth::id())
            ->whereIn('status', ['ended', 'paused'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('live-monitoring.index', compact('activeSessions', 'recentSessions'));
    }

    public function session(Request $request, string $sessionId): View
    {
        $session = LiveMonitoring::with(['user', 'transcripts'])
            ->where('session_id', $sessionId)
            ->firstOrFail();

        if ($session->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this session.');
        }

        return view('live-monitoring.session', compact('session'));
    }

    public function stream(Request $request, string $sessionId)
    {
        $session = LiveMonitoring::where('session_id', $sessionId)->firstOrFail();

        return response()->stream(function () use ($session) {
            $lastUpdate = null;

            while (true) {
                $session->refresh();

                if ($session->status === 'ended') {
                    echo "event: ended\n";
                    echo "data: " . json_encode(['message' => 'Session ended']) . "\n\n";
                    ob_flush();
                    flush();
                    break;
                }

                $data = [
                    'transcript' => $session->transcript_text ?? '',
                    'transcripts' => $session->transcripts->map(fn($t) => [
                        'text' => $t->text,
                        'speaker' => $t->speaker,
                        'start_time' => $t->start_time,
                    ])->toArray(),
                    'suggestions' => $session->active_suggestions ?? [],
                    'ztp_alerts' => $session->ztp_alerts ?? [],
                    'status' => $session->status,
                    'updated_at' => $session->updated_at->toIso8601String(),
                ];

                $dataHash = md5(json_encode($data));
                
                if ($dataHash !== $lastUpdate) {
                    echo "data: " . json_encode($data) . "\n\n";
                    ob_flush();
                    flush();
                    $lastUpdate = $dataHash;
                }

                if (connection_aborted()) {
                    break;
                }

                usleep(500000);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function start(Request $request)
    {
        try {
            $validated = $request->validate([
                'ctm_call_id' => 'nullable|string',
                'caller_number' => 'nullable|string',
                'agent_name' => 'nullable|string',
            ]);

            $session = $this->monitorService->startSession(
                Auth::id(),
                $validated['ctm_call_id'] ?? null,
                $validated['caller_number'] ?? null,
                $validated['agent_name'] ?? Auth::user()->name
            );

            return response()->json([
                'success' => true,
                'session_id' => $session->session_id,
                'session' => $session,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to start live monitoring session', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to start session: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function stop(Request $request, string $sessionId)
    {
        try {
            $session = LiveMonitoring::where('session_id', $sessionId)->firstOrFail();

            if ($session->user_id !== Auth::id()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $this->monitorService->endSession($session);

            return response()->json([
                'success' => true,
                'message' => 'Session ended successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to stop live monitoring session', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to stop session: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function addTranscript(Request $request)
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|string',
                'text' => 'required|string',
                'speaker' => 'nullable|string',
            ]);

            $session = LiveMonitoring::where('session_id', $validated['session_id'])->firstOrFail();

            $this->monitorService->processTranscriptChunk(
                $session,
                $validated['text'],
                [],
                $validated['speaker'] ?? 'unknown'
            );

            return response()->json([
                'success' => true,
                'message' => 'Transcript added',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add transcript', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to add transcript: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function chat(Request $request)
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|string',
                'question' => 'required|string',
            ]);

            $session = LiveMonitoring::where('session_id', $validated['session_id'])->firstOrFail();

            $answer = $this->monitorService->answerQuestion($session, $validated['question']);

            return response()->json([
                'success' => true,
                'answer' => $answer,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process chat', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'answer' => 'I apologize, I could not process that request. Please try again.',
            ], 500);
        }
    }
}
