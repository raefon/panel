<?php

namespace Kubectyl\Exceptions\Service\Snapshot;

use Kubectyl\Exceptions\DisplayException;

class TooManySnapshotsException extends DisplayException
{
    /**
     * TooManySnapshotsException constructor.
     */
    public function __construct(int $snapshotLimit)
    {
        parent::__construct(
            sprintf('Cannot create a new snapshot, this server has reached its limit of %d snapshots.', $snapshotLimit)
        );
    }
}
