<?php

namespace Kubectyl\Contracts\Repository;

use Kubectyl\Models\Cluster;
use Illuminate\Support\Collection;

interface ClusterRepositoryInterface extends RepositoryInterface
{
    public const THRESHOLD_PERCENTAGE_LOW = 75;
    public const THRESHOLD_PERCENTAGE_MEDIUM = 90;

    /**
     * Return a single cluster with location and server information.
     */
    public function loadLocationAndServerCount(Cluster $cluster, bool $refresh = false): Cluster;

    /**
     * Attach a paginated set of allocations to a cluster mode including
     * any servers that are also attached to those allocations.
     */
    public function loadClusterAllocations(Cluster $cluster, bool $refresh = false): Cluster;

    /**
     * Return a collection of clusters for all locations to use in server creation UI.
     */
    public function getClustersForServerCreation(): Collection;
}
