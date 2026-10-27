<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\AttendanceSession;
use App\Models\SessionSchedule;
use Illuminate\Console\Command;

class SyncGroupSessionCounts extends Command
{
    protected $signature = 'groups:sync-sessions
                            {--dry-run : Show what would be updated without changing data}';

    protected $description = 'Sync completed_sessions and total_sessions counts for all groups based on actual data';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info($isDryRun ? '[Dry Run] Calculating session counts...' : 'Syncing session counts for all groups...');

        $groups = Group::withTrashed(false)->get();
        $updated = 0;
        $skipped = 0;

        $this->withProgressBar($groups, function (Group $group) use ($isDryRun, &$updated, &$skipped) {
            $completedCount = AttendanceSession::where('group_id', $group->id)->count();
            $totalCount     = SessionSchedule::where('group_id', $group->id)->count();

            $needsUpdate = $group->completed_sessions !== $completedCount
                        || $group->total_sessions     !== $totalCount;

            if ($needsUpdate) {
                if (!$isDryRun) {
                    $group->update([
                        'completed_sessions' => $completedCount,
                        'total_sessions'     => $totalCount,
                    ]);
                }
                $this->newLine();
                $this->line("  [{$group->id}] {$group->name}: {$group->completed_sessions}/{$group->total_sessions} → {$completedCount}/{$totalCount}");
                $updated++;
            } else {
                $skipped++;
            }
        });

        $this->newLine(2);
        $this->info("Done! Updated: {$updated} | Already correct: {$skipped}");

        return self::SUCCESS;
    }
}
