<?php

namespace App\Http\Controllers;

use App\Models\Call;
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

        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->where('call_datetime', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->where('call_datetime', '<=', $request->date_to);
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $calls = $query->orderBy('call_datetime', 'desc')->paginate(20);

        return view('calls.index', compact('calls'));
    }

    public function show(string $ctmCallId)
    {
        $call = Call::with(['qaLog', 'user'])->where('ctm_call_id', $ctmCallId)->first();

        if (!$call) {
            return redirect()->route('calls.index')->with('error', 'Call not found');
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
                    'direction' => $ctmCall['direction'] ?? 'inbound',
                    'duration' => $ctmCall['duration'] ?? 0,
                    'call_datetime' => isset($ctmCall['timestamp']) 
                        ? \Carbon\Carbon::parse($ctmCall['timestamp']) 
                        : now(),
                    'agent_id' => $ctmCall['agent_id'] ?? null,
                    'agent_name' => $ctmCall['agent_name'] ?? null,
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
