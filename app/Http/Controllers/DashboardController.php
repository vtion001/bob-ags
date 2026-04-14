<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\QaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_calls' => 0,
            'analyzed_calls' => 0,
            'pending_calls' => 0,
            'avg_score' => 0,
            'qualified_leads' => 0,
            'ztp_failures' => 0,
        ];

        $recentCalls = [];

        try {
            $stats['total_calls'] = Call::count();
            $stats['analyzed_calls'] = Call::where('status', 'analyzed')->count();
            $stats['pending_calls'] = Call::where('status', 'pending')->count();
            $stats['ztp_failures'] = QaLog::where('ztp_failed', true)->count();

            $avgScore = QaLog::avg('total_score');
            $stats['avg_score'] = $avgScore ? round($avgScore, 1) : 0;

            $stats['qualified_leads'] = QaLog::where('disposition', 'qualified')->count();

            $recentCalls = Call::with(['qaLog'])
                ->orderBy('call_datetime', 'desc')
                ->limit(10)
                ->get();

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

        } catch (\Exception $e) {
            Log::error('Dashboard error', ['error' => $e->getMessage()]);
        }

        return view('dashboard', compact('stats', 'recentCalls'));
    }
}
