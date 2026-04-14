<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use App\Models\Call;
use App\Services\CTMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    protected CTMService $ctm;

    public function __construct(CTMService $ctm)
    {
        $this->ctm = $ctm;
    }

    public function index()
    {
        $agents = Agent::with('user')
            ->orderBy('ctm_agent_name')
            ->get();

        $users = User::whereIn('role', ['qa', 'admin', 'viewer'])
            ->orderBy('name')
            ->get();

        $linkedAgents = Agent::whereNotNull('user_id')->with('user')->get();

        return view('agents.index', compact('agents', 'users', 'linkedAgents'));
    }

    public function show($id)
    {
        $agent = Agent::with('user')->findOrFail($id);
        
        $calls = Call::where('agent_id', $agent->ctm_agent_id)
            ->with('qaLog')
            ->orderBy('call_datetime', 'desc')
            ->paginate(20);

        $totalCalls = Call::where('agent_id', $agent->ctm_agent_id)->count();
        $analyzedCalls = Call::where('agent_id', $agent->ctm_agent_id)
            ->whereNotNull('transcript_text')
            ->count();

        return view('agents.show', compact('agent', 'calls', 'totalCalls', 'analyzedCalls'));
    }

    public function sync()
    {
        try {
            $ctmCalls = $this->ctm->getCalls([
                'limit' => 1000,
                'start_date' => now()->subDays(90)->startOfDay()->toIso8601String(),
                'end_date' => now()->endOfDay()->toIso8601String(),
            ]);

            if (!$ctmCalls || !isset($ctmCalls['calls'])) {
                return redirect()->back()->with('error', 'No calls received from CTM');
            }

            $uniqueAgents = [];
            foreach ($ctmCalls['calls'] as $call) {
                if (!empty($call['agent_id']) && !isset($uniqueAgents[$call['agent_id']])) {
                    $uniqueAgents[$call['agent_id']] = [
                        'ctm_agent_id' => $call['agent_id'],
                        'ctm_agent_email' => $call['agent']['email'] ?? null,
                        'ctm_agent_name' => $call['agent']['name'] ?? 'Unknown Agent',
                    ];
                }
            }

            $synced = 0;
            foreach ($uniqueAgents as $agentData) {
                Agent::updateOrCreate(
                    ['ctm_agent_id' => $agentData['ctm_agent_id']],
                    [
                        'ctm_agent_email' => $agentData['ctm_agent_email'],
                        'ctm_agent_name' => $agentData['ctm_agent_name'],
                    ]
                );
                $synced++;
            }

            return redirect()->back()->with('success', "Synced {$synced} agents from CTM");
        } catch (\Exception $e) {
            Log::error('Agent sync error', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to sync agents: ' . $e->getMessage());
        }
    }

    public function link(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $agent = Agent::findOrFail($id);
        
        $existingLink = Agent::where('user_id', $validated['user_id'])->first();
        if ($existingLink && $existingLink->id !== $agent->id) {
            return redirect()->back()->with('error', 'User is already linked to another agent');
        }

        $agent->update(['user_id' => $validated['user_id']]);

        return redirect()->back()->with('success', "Linked {$agent->ctm_agent_name} to user successfully");
    }

    public function unlink($id)
    {
        $agent = Agent::findOrFail($id);
        $agent->update(['user_id' => null]);

        return redirect()->back()->with('success', "Unlinked {$agent->ctm_agent_name} from user");
    }

    public function getAgents()
    {
        $agents = Agent::with('user')->get();
        
        return response()->json($agents);
    }
}
