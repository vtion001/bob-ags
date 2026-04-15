<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\Agent;
use App\Models\QaLog;
use App\Services\CTMService;
use App\Services\AssemblyAIService;
use App\Services\QAAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    protected CTMService $ctm;
    protected AssemblyAIService $assemblyAI;
    protected QAAnalysisService $qa;

    public function __construct(
        CTMService $ctm,
        AssemblyAIService $assemblyAI,
        QAAnalysisService $qa
    ) {
        $this->ctm = $ctm;
        $this->assemblyAI = $assemblyAI;
        $this->qa = $qa;
    }

    public function index(Request $request)
    {
        $query = Call::query()->with('qaLog');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('date_from')) {
            $query->where('call_datetime', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('call_datetime', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('duration_min')) {
            $query->where('duration', '>=', (int) $request->duration_min);
        }

        if ($request->filled('score_min')) {
            $query->whereHas('qaLog', fn($q) => $q->where('total_score', '>=', (int) $request->score_min));
        }

        if ($request->filled('disposition')) {
            $query->whereHas('qaLog', fn($q) => $q->where('disposition', 'like', '%' . $request->disposition . '%'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $agents = Agent::with('user')->get();

        $calls = $query->orderBy('call_datetime', 'desc')->paginate(20);

        return view('calls.index', compact('calls', 'agents'));
    }

    public function searchCTM(Request $request)
    {
        try {
            $dateFrom    = $request->input('date_from', now()->subMonths(6)->toDateString());
            $dateTo      = $request->input('date_to', now()->toDateString());
            $search      = $request->input('search', '');
            $agentId     = $request->input('agent_id', '');
            $direction   = $request->input('direction', '');
            $durationMin = (int) $request->input('duration_min', 0);
            $scoreMin    = $request->filled('score_min') ? (int) $request->input('score_min') : null;
            $disposition = $request->input('disposition', '');
            $limit       = min((int) $request->input('limit', 500), 1000);

            $startDate = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $endDate   = \Carbon\Carbon::parse($dateTo)->endOfDay();

            $ctmCalls = $this->ctm->getCalls([
                'limit'      => $limit,
                'start_date' => $startDate->toIso8601String(),
                'end_date'   => $endDate->toIso8601String(),
            ]);

            if (!$ctmCalls || !isset($ctmCalls['calls'])) {
                return redirect()->back()->with('error', 'No calls received from CTM');
            }

            $calls = collect($ctmCalls['calls']);

            if (!empty($search)) {
                $searchNormalized = preg_replace('/[^0-9]/', '', $search);
                $calls = $calls->filter(function ($call) use ($searchNormalized, $search) {
                    $callerNumber   = preg_replace('/[^0-9]/', '', $call['caller_number'] ?? '');
                    $trackingNumber = preg_replace('/[^0-9]/', '', $call['tracking_number'] ?? '');
                    return stripos($callerNumber, $searchNormalized) !== false
                        || stripos($trackingNumber, $searchNormalized) !== false
                        || stripos($call['caller_number'] ?? '', $search) !== false
                        || stripos($call['tracking_number'] ?? '', $search) !== false;
                });
            }

            if (!empty($agentId)) {
                $calls = $calls->filter(fn($call) => ($call['agent_id'] ?? '') === $agentId);
            }

            if (!empty($direction)) {
                $calls = $calls->filter(fn($call) => ($call['direction'] ?? '') === $direction);
            }

            if ($durationMin > 0) {
                $calls = $calls->filter(fn($call) => ($call['duration'] ?? 0) >= $durationMin);
            }

            $totalEntries  = $ctmCalls['total_entries'] ?? count($ctmCalls['calls']);
            $filteredCount = $calls->count();

            // Load local records for cross-reference (score/disposition live here)
            $localQuery = Call::query()->with('qaLog');
            if (!empty($search)) {
                $localQuery->search($search);
            }
            if (!empty($agentId)) {
                $localQuery->where('agent_id', $agentId);
            }
            $localCalls = $localQuery->get()->keyBy('ctm_call_id');

            // Score and disposition filters apply only to locally-synced calls
            if ($scoreMin !== null || !empty($disposition)) {
                $calls = $calls->filter(function ($call) use ($localCalls, $scoreMin, $disposition) {
                    $callId = $call['id'] ?? null;
                    $local  = $localCalls->get($callId);
                    if (!$local) {
                        return false;
                    }
                    if ($scoreMin !== null && ($local->qaLog?->total_score ?? 0) < $scoreMin) {
                        return false;
                    }
                    if (!empty($disposition) && stripos($local->qaLog?->disposition ?? '', $disposition) === false) {
                        return false;
                    }
                    return true;
                });
                $filteredCount = $calls->count();
            }

            $agents = Agent::with('user')->get();

            return view('calls.index', [
                'calls'         => $calls->take(20),
                'localCalls'    => $localCalls,
                'totalEntries'  => $totalEntries,
                'filteredCount' => $filteredCount,
                'searchFrom'    => 'ctm',
                'dateFrom'      => $dateFrom,
                'dateTo'        => $dateTo,
                'agents'        => $agents,
            ]);
        } catch (\Exception $e) {
            Log::error('CTM Search error', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to search CTM: ' . $e->getMessage());
        }
    }

    public function show(string $ctmCallId)
    {
        $call = Call::with(['qaLog', 'user'])->where('ctm_call_id', $ctmCallId)->first();

        if (!$call) {
            return redirect()->route('calls.index')->with('error', 'Call not found');
        }

        // Fetch fresh CTM data to fill in any missing fields from old/incomplete syncs
        if (!$call->call_datetime || !$call->agent_name || !$call->recording_url) {
            $ctmData = $this->ctm->getCall($ctmCallId);
            if ($ctmData) {
                $updates = [];
                if (!$call->call_datetime) {
                    $rawDate = $ctmData['called_at'] ?? $ctmData['timestamp'] ?? null;
                    if ($rawDate) {
                        $updates['call_datetime'] = \Carbon\Carbon::parse($rawDate);
                    }
                }
                if (!$call->agent_name) {
                    $updates['agent_name'] = $ctmData['agent_name'] ?? ($ctmData['agent']['name'] ?? null);
                }
                if (!$call->recording_url && !empty($ctmData['recording_url'])) {
                    $updates['recording_url'] = $ctmData['recording_url'];
                }
                if (!empty($updates)) {
                    $call->update($updates);
                    $call->refresh();
                }
            }
        }

        if (empty($call->transcript_text)) {
            $transcriptData = $this->ctm->getCallTranscript($ctmCallId);
            if ($transcriptData && !empty($transcriptData['transcript'])) {
                $call->update(['transcript_text' => $transcriptData['transcript']]);
                $call->refresh();
            }
        }

        return view('calls.show', compact('call'));
    }

    public function sync(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from', now()->subDays(7)->toDateString());
            $dateTo = $request->input('date_to', now()->toDateString());
            $limit = $request->input('limit', 100);

            $startDate = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $endDate = \Carbon\Carbon::parse($dateTo)->endOfDay();

            $ctmCalls = $this->ctm->getCalls([
                'limit' => $limit,
                'start_date' => $startDate->toIso8601String(),
                'end_date' => $endDate->toIso8601String(),
            ]);

            if (!$ctmCalls || !isset($ctmCalls['calls'])) {
                return redirect()->back()->with('error', 'No calls received from CTM');
            }

            $synced = 0;
            $skipped = 0;

            foreach ($ctmCalls['calls'] as $ctmCall) {
                $existingCall = Call::where('ctm_call_id', $ctmCall['id'])->first();

                $callData = [
                    'ctm_call_id' => $ctmCall['id'],
                    'tracking_number' => $ctmCall['phone'] ?? null,
                    'caller_number' => $ctmCall['caller_number'] ?? null,
                    'direction' => in_array($ctmCall['direction'] ?? '', ['inbound', 'outbound']) ? $ctmCall['direction'] : 'inbound',
                    'duration' => $ctmCall['duration'] ?? 0,
                    'call_datetime' => isset($ctmCall['called_at'])
                        ? \Carbon\Carbon::parse($ctmCall['called_at'])
                        : (isset($ctmCall['timestamp']) ? \Carbon\Carbon::parse($ctmCall['timestamp']) : null),
                    'agent_id' => $ctmCall['agent_id'] ?? ($ctmCall['agent']['id'] ?? null),
                    'agent_name' => $ctmCall['agent_name'] ?? ($ctmCall['agent']['name'] ?? null),
                    'source' => $ctmCall['source'] ?? null,
                    'tracking_label' => $ctmCall['tracking_label'] ?? null,
                    'recording_url' => $ctmCall['recording_url'] ?? null,
                    'caller_city' => $ctmCall['city'] ?? null,
                    'caller_state' => $ctmCall['state'] ?? null,
                ];

                if ($existingCall) {
                    $existingCall->update($callData);
                    $skipped++;
                } else {
                    $callData['status'] = 'pending';
                    Call::create($callData);
                    $synced++;
                }
            }

            $message = "Synced {$synced} new calls from CTM";
            if ($skipped > 0) {
                $message .= " ({$skipped} existing calls updated)";
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('CTM Sync error', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to sync calls: ' . $e->getMessage());
        }
    }

    public function analyze(string $ctmCallId)
    {
        try {
            $call = Call::where('ctm_call_id', $ctmCallId)->first();

            if (!$call) {
                return redirect()->back()->with('error', 'Call not found');
            }

            $call->update(['status' => 'analyzing']);

            if (empty($call->transcript_text)) {
                $transcriptData = $this->ctm->getCallTranscript($ctmCallId);
                
                if ($transcriptData && !empty($transcriptData['transcript'])) {
                    $call->update([
                        'transcript_text' => $transcriptData['transcript'],
                        'status' => 'transcribed',
                    ]);
                } else {
                    $call->update(['status' => 'pending']);
                    return redirect()->back()->with('error', 'No transcript available for this call');
                }
            }

            $analysis = $this->qa->analyzeTranscript($ctmCallId, $call->transcript_text);

            $call->update(['status' => 'analyzed']);

            QaLog::updateOrCreate(
                ['call_id' => $call->id],
                [
                    'analyst_id' => Auth::id(),
                    'total_score' => $analysis['score'],
                    'ztp_failed' => count($analysis['ztp_violations']) > 0,
                    'sentiment' => $analysis['sentiment'],
                    'disposition' => $analysis['disposition'],
                    'criteria_scores' => $analysis['rubric_results'],
                    'rubric_breakdown' => $analysis['rubric_breakdown'],
                    'ztp_violations' => $analysis['ztp_violations'],
                    'notes' => $analysis['summary'],
                ]
            );

            return redirect()->route('calls.show', $ctmCallId)->with('success', 'Analysis completed. Score: ' . $analysis['score'] . '/100');
        } catch (\Exception $e) {
            Log::error('Analysis error', ['call_id' => $ctmCallId, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Analysis failed: ' . $e->getMessage());
        }
    }

    public function transcribe(string $ctmCallId)
    {
        try {
            $call = Call::where('ctm_call_id', $ctmCallId)->first();

            if (!$call) {
                return redirect()->back()->with('error', 'Call not found');
            }

            if (empty($call->recording_url)) {
                return redirect()->back()->with('error', 'No recording URL available for this call');
            }

            $transcript = $this->assemblyAI->transcribe($call->recording_url);

            if ($transcript && isset($transcript['id'])) {
                $call->update([
                    'transcript_id' => $transcript['id'],
                    'status' => 'transcribed',
                ]);

                $polledTranscript = $this->assemblyAI->pollTranscript($transcript['id']);

                if ($polledTranscript && $polledTranscript['status'] === 'completed') {
                    $words = $this->assemblyAI->getWords($transcript['id']);
                    
                    $call->update([
                        'transcript_text' => $polledTranscript['text'] ?? '',
                        'transcript_json' => $words ?? null,
                    ]);
                }
            }

            return redirect()->route('calls.show', $ctmCallId)->with('success', 'Transcription completed');
        } catch (\Exception $e) {
            Log::error('Transcription error', ['call_id' => $ctmCallId, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Transcription failed: ' . $e->getMessage());
        }
    }
}
