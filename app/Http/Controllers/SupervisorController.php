<?php

namespace App\Http\Controllers;

use App\Models\LiveMonitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupervisorController extends Controller
{
    public function index(Request $request): View
    {
        $activeSessions = LiveMonitoring::with('user')
            ->where('status', 'active')
            ->orderBy('started_at', 'desc')
            ->get();

        $ztpAlerts = LiveMonitoring::with('user')
            ->where('status', 'active')
            ->whereNotNull('ztp_alerts')
            ->get()
            ->flatMap(fn($session) => collect($session->ztp_alerts ?? [])
                ->map(fn($alert) => array_merge($alert, [
                    'session_id' => $session->session_id,
                    'agent_name' => $session->agent_name,
                    'user' => $session->user,
                ])))
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();

        return view('supervisor.index', compact('activeSessions', 'ztpAlerts'));
    }

    public function liveStream(Request $request)
    {
        return response()->stream(function () {
            $lastUpdate = null;

            while (true) {
                $activeSessions = LiveMonitoring::with('user')
                    ->where('status', 'active')
                    ->get();

                $sessions = $activeSessions->map(fn($session) => [
                    'id' => $session->id,
                    'session_id' => $session->session_id,
                    'agent_name' => $session->agent_name,
                    'caller_number' => $session->caller_number,
                    'transcript_preview' => Str::limit($session->transcript_text ?? '', 200),
                    'ztp_alerts' => $session->ztp_alerts ?? [],
                    'active_suggestions' => $session->active_suggestions ?? [],
                    'started_at' => $session->started_at?->toIso8601String(),
                    'user' => [
                        'id' => $session->user?->id,
                        'name' => $session->user?->name,
                    ],
                ])->toArray();

                $data = [
                    'active_count' => $activeSessions->count(),
                    'sessions' => $sessions,
                    'timestamp' => now()->toIso8601String(),
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

                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
