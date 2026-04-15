<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\Call;
use Illuminate\Console\Command;

class MergeDuplicateAgents extends Command
{
    protected $signature = 'agents:merge-duplicates';

    protected $description = 'Merge duplicate agent records that share the same ctm_agent_id, keeping the most complete record';

    public function handle(): int
    {
        $duplicates = Agent::select('ctm_agent_id')
            ->groupBy('ctm_agent_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('ctm_agent_id');

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate agents found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$duplicates->count()} ctm_agent_id(s) with duplicate records.");

        $totalMerged = 0;

        foreach ($duplicates as $ctmAgentId) {
            $records = Agent::where('ctm_agent_id', $ctmAgentId)->get();

            $best = $records->sortByDesc(fn ($r) => [
                $r->ctm_agent_name !== 'Unknown' && $r->ctm_agent_name !== null,
                $r->ctm_agent_email !== null,
                $r->user_group !== null,
                $r->user_id !== null,
            ])->first();

            $toDelete = $records->reject(fn ($r) => $r->id === $best->id);

            $this->info("  Merging ctm_agent_id {$ctmAgentId}:");
            $this->info("    Keeping ID {$best->id} ({$best->ctm_agent_name} / {$best->ctm_agent_email} / {$best->user_group})");

            foreach ($toDelete as $dup) {
                $callCount = Call::where('agent_id', $dup->ctm_agent_id)->count();
                $this->info("    Deleting ID {$dup->id} ({$dup->ctm_agent_name} / {$dup->ctm_agent_email}) - {$callCount} calls reassigned");

                Call::where('agent_id', $dup->ctm_agent_id)
                    ->where('user_id', '!=', $best->user_id)
                    ->update(['user_id' => $best->user_id]);

                $dup->delete();
                $totalMerged++;
            }
        }

        $this->info("Done. Deleted {$totalMerged} duplicate agent record(s).");

        return Command::SUCCESS;
    }
}
