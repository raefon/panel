<?php

namespace Kubectyl\Services\Snapshots;

use Ramsey\Uuid\Uuid;
use Carbon\CarbonImmutable;
use Kubectyl\Models\Server;
use Kubectyl\Models\Snapshot;
use Illuminate\Database\ConnectionInterface;
use Kubectyl\Extensions\Snapshots\SnapshotManager;
use Kubectyl\Repositories\Eloquent\SnapshotRepository;
use Kubectyl\Repositories\Kuber\DaemonSnapshotRepository;
use Kubectyl\Exceptions\Service\Snapshot\TooManySnapshotsException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class InitiateSnapshotService
{
    private bool $isLocked = false;

    /**
     * InitiateSnapshotService constructor.
     */
    public function __construct(
        private SnapshotRepository $repository,
        private ConnectionInterface $connection,
        private DaemonSnapshotRepository $daemonSnapshotRepository,
        private DeleteSnapshotService $deleteSnapshotService,
        private SnapshotManager $snapshotManager
    ) {
    }

    /**
     * Set if the snapshot should be locked once it is created which will prevent
     * its deletion by users or automated system processes.
     */
    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * Initiates the snapshot process for a server on Wings.
     *
     * @throws \Throwable
     * @throws \Kubectyl\Exceptions\Service\Snapshot\TooManySnapshotsException
     * @throws \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     */
    public function handle(Server $server, string $name = null, bool $override = false): Snapshot
    {
        $limit = config('snapshots.throttles.limit');
        $period = config('snapshots.throttles.period');
        if ($period > 0) {
            $previous = $this->repository->getSnapshotsGeneratedDuringTimespan($server->id, $period);
            if ($previous->count() >= $limit) {
                $message = sprintf('Only %d snapshots may be generated within a %d second span of time.', $limit, $period);

                throw new TooManyRequestsHttpException(CarbonImmutable::now()->diffInSeconds($previous->last()->created_at->addSeconds($period)), $message);
            }
        }

        // Check if the server has reached or exceeded its snapshot limit.
        // completed_at == null will cover any ongoing snapshots, while is_successful == true will cover any completed snapshots.
        $successful = $this->repository->getNonFailedSnapshots($server);
        if (!$server->snapshot_limit || $successful->count() >= $server->snapshot_limit) {
            // Do not allow the user to continue if this server is already at its limit and can't override.
            if (!$override || $server->snapshot_limit <= 0) {
                throw new TooManySnapshotsException($server->snapshot_limit);
            }

            // Get the oldest snapshot the server has that is not "locked" (indicating a snapshot that should
            // never be automatically purged). If we find a snapshot we will delete it and then continue with
            // this process. If no snapshot is found that can be used an exception is thrown.
            /** @var \Kubectyl\Models\Snapshot $oldest */
            $oldest = $successful->where('is_locked', false)->orderBy('created_at')->first();
            if (!$oldest) {
                throw new TooManySnapshotsException($server->snapshot_limit);
            }

            $this->deleteSnapshotService->handle($oldest);
        }

        return $this->connection->transaction(function () use ($server, $name) {
            /** @var \Kubectyl\Models\Snapshot $snapshot */
            $snapshot = $this->repository->create([
                'server_id' => $server->id,
                'uuid' => Uuid::uuid4()->toString(),
                'name' => trim($name) ?: sprintf('Snapshot at %s', CarbonImmutable::now()->toDateTimeString()),
                'disk' => $this->snapshotManager->getDefaultAdapter(),
                'is_locked' => $this->isLocked,
            ], true, true);

            $this->daemonSnapshotRepository->setServer($server)
                ->setSnapshotAdapter($this->snapshotManager->getDefaultAdapter())
                ->snapshot($snapshot);

            return $snapshot;
        });
    }
}
