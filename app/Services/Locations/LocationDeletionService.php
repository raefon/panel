<?php

namespace Kubectyl\Services\Locations;

use Webmozart\Assert\Assert;
use Kubectyl\Models\Location;
use Kubectyl\Contracts\Repository\ClusterRepositoryInterface;
use Kubectyl\Contracts\Repository\LocationRepositoryInterface;
use Kubectyl\Exceptions\Service\Location\HasActiveClustersException;

class LocationDeletionService
{
    /**
     * LocationDeletionService constructor.
     */
    public function __construct(
        protected LocationRepositoryInterface $repository,
        protected ClusterRepositoryInterface $clusterRepository
    ) {
    }

    /**
     * Delete an existing location.
     *
     * @throws \Kubectyl\Exceptions\Service\Location\HasActiveClustersException
     */
    public function handle(Location|int $location): ?int
    {
        $location = ($location instanceof Location) ? $location->id : $location;

        Assert::integerish($location, 'First argument passed to handle must be numeric or an instance of ' . Location::class . ', received %s.');

        $count = $this->clusterRepository->findCountWhere([['location_id', '=', $location]]);
        if ($count > 0) {
            throw new HasActiveClustersException(trans('exceptions.locations.has_clusters'));
        }

        return $this->repository->delete($location);
    }
}
