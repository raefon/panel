<?php

namespace Kubectyl\Contracts\Repository;

use Kubectyl\Models\Allocation;

interface AllocationRepositoryInterface extends RepositoryInterface
{
    /**
     * Return all the allocations that exist for a cluster that are not currently
     * allocated.
     */
    public function getUnassignedAllocationIds(int $cluster): array;

    /**
     * Return a single allocation from those meeting the requirements.
     */
    public function getRandomAllocation(array $clusters, array $ports, bool $dedicated = false): ?Allocation;
}
