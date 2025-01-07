<?php

namespace Kubectyl\Repositories\Eloquent;

use Kubectyl\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Kubectyl\Exceptions\Repository\RecordNotFoundException;
use Kubectyl\Contracts\Repository\LocationRepositoryInterface;

class LocationRepository extends EloquentRepository implements LocationRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return Location::class;
    }

    /**
     * Return locations with a count of clusters and servers attached to it.
     */
    public function getAllWithDetails(): Collection
    {
        return $this->getBuilder()->withCount('clusters', 'servers')->get($this->getColumns());
    }

    /**
     * Return all the available locations with the clusters as a relationship.
     */
    public function getAllWithClusters(): Collection
    {
        return $this->getBuilder()->with('clusters')->get($this->getColumns());
    }

    /**
     * Return all the clusters and their respective count of servers for a location.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithClusters(int $id): Location
    {
        try {
            return $this->getBuilder()->with('clusters.servers')->findOrFail($id, $this->getColumns());
        } catch (ModelNotFoundException) {
            throw new RecordNotFoundException();
        }
    }

    /**
     * Return a location and the count of clusters in that location.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithClusterCount(int $id): Location
    {
        try {
            return $this->getBuilder()->withCount('clusters')->findOrFail($id, $this->getColumns());
        } catch (ModelNotFoundException) {
            throw new RecordNotFoundException();
        }
    }
}
