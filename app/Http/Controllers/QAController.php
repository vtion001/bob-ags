<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\QaLog;
use Illuminate\Http\Request;

class QAController extends Controller
{
    public function logs(Request $request)
    {
        $query = QaLog::with(['call', 'analyst']);

        if ($request->has('date_from') && ! empty($request->date_from)) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && ! empty($request->date_to)) {
            $query->where('created_at', '<=', $request->date_to);
        }

        if ($request->has('sentiment') && ! empty($request->sentiment)) {
            $query->where('sentiment', $request->sentiment);
        }

        if ($request->has('ztp_failed') && $request->ztp_failed === '1') {
            $query->where('ztp_failed', true);
        }

        if ($request->has('disposition') && ! empty($request->disposition)) {
            $query->where('disposition', $request->disposition);
        }

        $qaLogs = $query->orderBy('created_at', 'desc')->paginate(25);

        $calls = $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($qa) => [
                'ctm_call_id' => $qa->call?->ctm_call_id,
                'timestamp' => $qa->call?->call_datetime,
                'score' => $qa->total_score,
                'sentiment' => $qa->sentiment,
                'disposition' => $qa->disposition,
                'tags' => $qa->notes,
                'isQA' => true,
                '_qa' => $qa,
            ]);

        $stats = QaLog::selectRaw('
            COUNT(*) as total,
            AVG(total_score) as avg_score,
            SUM(CASE WHEN ztp_failed = true THEN 1 ELSE 0 END) as ztp_failures,
            SUM(CASE WHEN sentiment = "positive" THEN 1 ELSE 0 END) as positive,
            SUM(CASE WHEN sentiment = "neutral" THEN 1 ELSE 0 END) as neutral,
            SUM(CASE WHEN sentiment = "negative" THEN 1 ELSE 0 END) as negative
        ')->first();

        $scoreDistribution = [
            'excellent' => QaLog::whereBetween('total_score', [85, 100])->count(),
            'good' => QaLog::whereBetween('total_score', [70, 84.99])->count(),
            'needs_improvement' => QaLog::whereBetween('total_score', [50, 69.99])->count(),
            'poor' => QaLog::where('total_score', '<', 50)->count(),
        ];

        $dispositionCounts = QaLog::selectRaw('disposition, COUNT(*) as count')
            ->groupBy('disposition')
            ->pluck('count', 'disposition')
            ->toArray();

        return view('qa.logs', [
            'qaLogs' => $qaLogs,
            'calls' => $calls,
            'scoreDistribution' => $scoreDistribution,
            'dispositionCounts' => $dispositionCounts,
        ]);
    }

    public function show(string $callId)
    {
        $call = Call::with(['qaLog', 'qaLog.analyst'])->findOrFail($callId);

        if (! $call->qaLog) {
            return redirect()->route('qa.logs')->with('error', 'No QA analysis found for this call');
        }

        return view('qa.show', compact('call'));
    }
}
