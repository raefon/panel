<?php

namespace Kubectyl\Console\Commands\Maintenance;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Kubectyl\Repositories\Eloquent\SnapshotRepository;

class PruneOrphanedSnapshotsCommand extends Command
{
    protected $signature = 'p:maintenance:prune-snapshots {--prune-age=}';

    protected $description = 'Marks all snapshots that have not completed in the last "n" minutes as being failed.';

    /**
     * PruneOrphanedSnapshotsCommand constructor.
     */
    public function __construct(private SnapshotRepository $snapshotRepository)
    {
        parent::__construct();
    }

    public function handle()
    {
        $since = $this->option('prune-age') ?? config('snapshots.prune_age', 360);
        if (!$since || !is_digit($since)) {
            throw new \InvalidArgumentException('The "--prune-age" argument must be a value greater than 0.');
        }

        $query = $this->snapshotRepository->getBuilder()
            ->whereNull('completed_at')
            ->where('created_at', '<=', CarbonImmutable::now()->subMinutes($since)->toDateTimeString());

        $count = $query->count();
        if (!$count) {
            $this->info('There are no orphaned snapshots to be marked as failed.');

            return;
        }

        $this->warn("Marking $count snapshots that have not been marked as completed in the last $since minutes as failed.");

        $query->update([
            'is_successful' => false,
            'completed_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);
    }
}
