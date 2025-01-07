<?php

namespace Kubectyl\Exceptions\Service\Snapshot;

use Kubectyl\Exceptions\DisplayException;

class SnapshotLockedException extends DisplayException
{
    /**
     * TooManySnapshotsException constructor.
     */
    public function __construct()
    {
        parent::__construct('Cannot delete a snapshot that is marked as locked.');
    }
}
