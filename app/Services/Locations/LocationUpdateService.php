<?php

namespace Kubectyl\Services\Locations;

use Kubectyl\Models\Location;
use Kubectyl\Contracts\Repository\LocationRepositoryInterface;

class LocationUpdateService
{
    /**
     * LocationUpdateService constructor.
     */
    public function __construct(protected LocationRepositoryInterface $repository)
    {
    }

    /**
     * Update an existing location.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(Location|int $location, array $data): Location
    {
        $location = ($location instanceof Location) ? $location->id : $location;

        return $this->repository->update($location, $data);
    }
}
