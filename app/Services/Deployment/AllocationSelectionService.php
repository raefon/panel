<?php

namespace Kubectyl\Services\Deployment;

use Kubectyl\Models\Allocation;
use Kubectyl\Exceptions\DisplayException;
use Kubectyl\Services\Allocations\AssignmentService;
use Kubectyl\Contracts\Repository\AllocationRepositoryInterface;
use Kubectyl\Exceptions\Service\Deployment\NoViableAllocationException;

class AllocationSelectionService
{
    protected bool $dedicated = false;

    protected array $clusters = [];

    protected array $ports = [];

    /**
     * AllocationSelectionService constructor.
     */
    public function __construct(private AllocationRepositoryInterface $repository)
    {
    }

    /**
     * Toggle if the selected allocation should be the only allocation belonging
     * to the given IP address. If true an allocation will not be selected if an IP
     * already has another server set to use on if its allocations.
     */
    public function setDedicated(bool $dedicated): self
    {
        $this->dedicated = $dedicated;

        return $this;
    }

    /**
     * A list of cluster IDs that should be used when selecting an allocation. If empty, all
     * clusters will be used to filter with.
     */
    public function setClusters(array $clusters): self
    {
        $this->clusters = $clusters;

        return $this;
    }

    /**
     * An array of individual ports or port ranges to use when selecting an allocation. If
     * empty, all ports will be considered when finding an allocation. If set, only ports appearing
     * in the array or range will be used.
     *
     * @throws \Kubectyl\Exceptions\DisplayException
     */
    public function setPorts(array $ports): self
    {
        $stored = [];
        foreach ($ports as $port) {
            if (is_digit($port)) {
                $stored[] = $port;
            }

            // Ranges are stored in the ports array as an array which can be
            // better processed in the repository.
            if (preg_match(AssignmentService::PORT_RANGE_REGEX, $port, $matches)) {
                if (abs($matches[2] - $matches[1]) > AssignmentService::PORT_RANGE_LIMIT) {
                    throw new DisplayException(trans('exceptions.allocations.too_many_ports'));
                }

                $stored[] = [$matches[1], $matches[2]];
            }
        }

        $this->ports = $stored;

        return $this;
    }

    /**
     * Return a single allocation that should be used as the default allocation for a server.
     *
     * @throws \Kubectyl\Exceptions\Service\Deployment\NoViableAllocationException
     */
    public function handle(): Allocation
    {
        $allocation = $this->repository->getRandomAllocation($this->clusters, $this->ports, $this->dedicated);

        if (is_null($allocation)) {
            throw new NoViableAllocationException(trans('exceptions.deployment.no_viable_allocations'));
        }

        return $allocation;
    }
}
