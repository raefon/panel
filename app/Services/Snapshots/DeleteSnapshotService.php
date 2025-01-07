<?php

namespace Kubectyl\Services\Snapshots;

use Illuminate\Http\Response;
use Kubectyl\Models\Snapshot;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\ConnectionInterface;
use Kubectyl\Extensions\Snapshots\SnapshotManager;
use Kubectyl\Repositories\Kuber\DaemonSnapshotRepository;
use Kubectyl\Exceptions\Service\Snapshot\SnapshotLockedException;
use Kubectyl\Exceptions\Http\Connection\DaemonConnectionException;

class DeleteSnapshotService
{
    public function __construct(
        private ConnectionInterface $connection,
        private SnapshotManager $manager,
        private DaemonSnapshotRepository $daemonSnapshotRepository
    ) {
    }

    /**
     * Deletes a snapshot from the system. If the snapshot is stored in S3 a request
     * will be made to delete that snapshot from the disk as well.
     *
     * @throws \Throwable
     */
    public function handle(Snapshot $snapshot): void
    {
        // If the snapshot is marked as failed it can still be deleted, even if locked
        // since the UI doesn't allow you to unlock a failed snapshot in the first place.
        //
        // I also don't really see any reason you'd have a locked, failed snapshot to keep
        // around. The logic that updates the snapshot to the failed state will also remove
        // the lock, so this condition should really never happen.
        if ($snapshot->is_locked && ($snapshot->is_successful && !is_null($snapshot->completed_at))) {
            throw new SnapshotLockedException();
        }

        if ($snapshot->disk === Snapshot::ADAPTER_AWS_S3) {
            $this->deleteFromS3($snapshot);

            return;
        }

        $this->connection->transaction(function () use ($snapshot) {
            try {
                $this->daemonSnapshotRepository->setServer($snapshot->server)->delete($snapshot);
            } catch (DaemonConnectionException $exception) {
                $previous = $exception->getPrevious();
                // Don't fail the request if the Daemon responds with a 404, just assume the snapshot
                // doesn't actually exist and remove its reference from the Panel as well.
                if (!$previous instanceof ClientException || $previous->getResponse()->getStatusCode() !== Response::HTTP_NOT_FOUND) {
                    throw $exception;
                }
            }

            $snapshot->delete();
        });
    }

    /**
     * Deletes a snapshot from an S3 disk.
     *
     * @throws \Throwable
     */
    protected function deleteFromS3(Snapshot $snapshot): void
    {
        $this->connection->transaction(function () use ($snapshot) {
            $snapshot->delete();

            /** @var \Kubectyl\Extensions\Filesystem\S3Filesystem $adapter */
            $adapter = $this->manager->adapter(Snapshot::ADAPTER_AWS_S3);

            $adapter->getClient()->deleteObject([
                'Bucket' => $adapter->getBucket(),
                'Key' => sprintf('%s/%s.tar.gz', $snapshot->server->uuid, $snapshot->uuid),
            ]);
        });
    }
}
