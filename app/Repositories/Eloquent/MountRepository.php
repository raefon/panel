<?php

namespace Kubectyl\Repositories\Eloquent;

use Kubectyl\Models\Mount;
use Kubectyl\Models\Server;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Kubectyl\Exceptions\Repository\RecordNotFoundException;

class MountRepository extends EloquentRepository
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return Mount::class;
    }

    /**
     * Return mounts with a count of rockets, clusters, and servers attached to it.
     */
    public function getAllWithDetails(): Collection
    {
        return $this->getBuilder()->withCount('rockets', 'clusters')->get($this->getColumns());
    }

    /**
     * Return all the mounts and their respective relations.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithRelations(string $id): Mount
    {
        try {
            return $this->getBuilder()->with('rockets', 'clusters')->findOrFail($id, $this->getColumns());
        } catch (ModelNotFoundException $exception) {
            throw new RecordNotFoundException();
        }
    }

    /**
     * Return mounts available to a server (ignoring if they are or are not mounted).
     */
    public function getMountListForServer(Server $server): Collection
    {
        return $this->getBuilder()
            ->whereHas('rockets', function ($q) use ($server) {
                $q->where('id', '=', $server->rocket_id);
            })
            ->whereHas('clusters', function ($q) use ($server) {
                $q->where('id', '=', $server->cluster_id);
            })
            ->get($this->getColumns());
    }
}
