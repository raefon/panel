<?php

namespace Kubectyl\Services\Snapshots;

use Kubectyl\Models\User;
use Carbon\CarbonImmutable;
use Kubectyl\Models\Snapshot;
use Kubectyl\Services\Clusters\ClusterJWTService;
use Kubectyl\Extensions\Snapshots\SnapshotManager;

class DownloadLinkService
{
    /**
     * DownloadLinkService constructor.
     */
    public function __construct(private SnapshotManager $snapshotManager, private ClusterJWTService $jwtService)
    {
    }

    /**
     * Returns the URL that allows for a snapshot to be downloaded by an individual
     * user, or by the Wings control software.
     */
    public function handle(Snapshot $snapshot, User $user): string
    {
        if ($snapshot->disk === Snapshot::ADAPTER_AWS_S3) {
            return $this->getS3SnapshotUrl($snapshot);
        }

        $token = $this->jwtService
            ->setExpiresAt(CarbonImmutable::now()->addMinutes(15))
            ->setUser($user)
            ->setClaims([
                'snapshot_uuid' => $snapshot->uuid,
                'server_uuid' => $snapshot->server->uuid,
            ])
            ->handle($snapshot->server->cluster, $user->id . $snapshot->server->uuid);

        return sprintf('%s/download/snapshot?token=%s', $snapshot->server->cluster->getConnectionAddress(), $token->toString());
    }

    /**
     * Returns a signed URL that allows us to download a file directly out of a non-public
     * S3 bucket by using a signed URL.
     */
    protected function getS3SnapshotUrl(Snapshot $snapshot): string
    {
        /** @var \Kubectyl\Extensions\Filesystem\S3Filesystem $adapter */
        $adapter = $this->snapshotManager->adapter(Snapshot::ADAPTER_AWS_S3);

        $request = $adapter->getClient()->createPresignedRequest(
            $adapter->getClient()->getCommand('GetObject', [
                'Bucket' => $adapter->getBucket(),
                'Key' => sprintf('%s/%s.tar.gz', $snapshot->server->uuid, $snapshot->uuid),
                'ContentType' => 'application/x-gzip',
            ]),
            CarbonImmutable::now()->addMinutes(5)
        );

        return $request->getUri()->__toString();
    }
}
