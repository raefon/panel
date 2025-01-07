<?php

namespace Kubectyl\Contracts\Repository;

use Kubectyl\Models\Location;
use Illuminate\Support\Collection;

interface LocationRepositoryInterface extends RepositoryInterface
{
    /**
     * Return locations with a count of clusters and servers attached to it.
     */
    public function getAllWithDetails(): Collection;

    /**
     * Return all the available locations with the clusters as a relationship.
     */
    public function getAllWithClusters(): Collection;

    /**
     * Return all the clusters and their respective count of servers for a location.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithClusters(int $id): Location;

    /**
     * Return a location and the count of clusters in that location.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithClusterCount(int $id): Location;
}
