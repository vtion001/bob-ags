<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Call;
use App\Models\Setting;
use App\Models\User;
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

        $userGroups = $this->ctm->getUserGroups() ?? [];
        $savedEmailDomain = Setting::getValue('agent_sync_email_domain', '');
        $savedUserGroup = Setting::getValue('agent_sync_user_group', '');

        $agentsQuery = Agent::with('user')->orderBy('ctm_agent_name');

        if ($savedUserGroup) {
            $agentsQuery->where('user_group', $savedUserGroup);
        }

        if ($savedEmailDomain) {
            $agentsQuery->where('ctm_agent_email', 'like', '%'.$savedEmailDomain);
        }

        $agents = $agentsQuery->get();

        return view('agents.index', compact(
            'agents',
            'users',
            'linkedAgents',
            'userGroups',
            'savedEmailDomain',
            'savedUserGroup'
        ));
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

    public function sync(Request $request)
    {
        try {
            $emailDomain = Setting::getValue('agent_sync_email_domain', '');
            $userGroup = Setting::getValue('agent_sync_user_group', '');

            $ctmUsers = $this->ctm->getCTMUsers();

            if ($ctmUsers === null) {
                return redirect()->back()->with('error', 'Failed to fetch agents from CTM');
            }

            $synced = 0;
            foreach ($ctmUsers as $agentData) {
                Agent::updateOrCreate(
                    ['ctm_agent_id' => $agentData['ctm_agent_id']],
                    [
                        'ctm_agent_email' => $agentData['ctm_agent_email'],
                        'ctm_agent_name' => $agentData['ctm_agent_name'],
                        'user_group' => $agentData['user_group'] ?? null,
                    ]
                );
                $synced++;
            }

            $needsBackfill = Agent::where(function ($q) {
                $q->whereNull('user_group')
                    ->orWhere('ctm_agent_name', 'Unknown');
            })->whereNotIn('ctm_agent_id', array_column($ctmUsers, 'ctm_agent_id'))->get();

            $backfilled = 0;
            foreach ($needsBackfill as $agent) {
                $detail = $this->ctm->getAgentById($agent->ctm_agent_id);
                if ($detail) {
                    $updates = [];
                    if ($agent->ctm_agent_name === 'Unknown' && $detail['ctm_agent_name'] !== 'Unknown') {
                        $updates['ctm_agent_name'] = $detail['ctm_agent_name'];
                    }
                    if (! $agent->user_group && $detail['user_group']) {
                        $updates['user_group'] = $detail['user_group'];
                    }
                    if ($agent->ctm_agent_email === null && $detail['ctm_agent_email']) {
                        $updates['ctm_agent_email'] = $detail['ctm_agent_email'];
                    }
                    if ($updates) {
                        $agent->update($updates);
                        $backfilled++;
                    }
                }
            }

            $filterLabel = '';
            if ($emailDomain || $userGroup) {
                $parts = [];
                if ($emailDomain) {
                    $parts[] = "domain: {$emailDomain}";
                }
                if ($userGroup) {
                    $parts[] = "group: {$userGroup}";
                }
                $filterLabel = ' ('.implode(', ', $parts).')';
            }

            $message = "Synced {$synced} agents from CTM{$filterLabel}";
            if ($backfilled > 0) {
                $message .= " ({$backfilled} existing agents backfilled)";
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Agent sync error', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to sync agents: '.$e->getMessage());
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

    public function saveFilters(Request $request)
    {
        $emailDomain = $request->input('email_domain', '');
        $userGroup = $request->input('user_group', '');

        Setting::setValue('agent_sync_email_domain', $emailDomain);
        Setting::setValue('agent_sync_user_group', $userGroup);

        return redirect()->back()->with('success', 'Agent sync filters saved.');
    }

    public function getAgents()
    {
        $agents = Agent::with('user')->get();

        return response()->json($agents);
    }

    public function searchPhillies()
    {
        $keyword = 'phillies';

        $agents = $this->ctm->getCTMUsers($keyword);

        if (empty($agents)) {
            $agents = $this->ctm->getAgentsBySource('Phillies');
        }

        if ($agents === null) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch agents from CTM',
                'agents' => [],
            ], 502);
        }

        $ctmIds = array_column($agents, 'ctm_agent_id');
        $localAgents = Agent::whereIn('ctm_agent_id', $ctmIds)->get()->keyBy('ctm_agent_id');

        $results = array_map(function ($agent) use ($localAgents) {
            $local = $localAgents->get($agent['ctm_agent_id']);

            return array_merge($agent, [
                'local_agent_id' => $local?->id,
                'linked_user_id' => $local?->user_id,
                'is_local' => $local !== null,
                'is_linked' => $local?->isLinked() ?? false,
            ]);
        }, $agents);

        return response()->json([
            'success' => true,
            'count' => count($results),
            'keyword' => $keyword,
            'agents' => $results,
        ]);
    }
}
