<?php

namespace Kubectyl\Repositories\Eloquent;

use Kubectyl\Models\Server;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Kubectyl\Exceptions\Repository\RecordNotFoundException;
use Kubectyl\Contracts\Repository\ServerRepositoryInterface;

class ServerRepository extends EloquentRepository implements ServerRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return Server::class;
    }

    /**
     * Load the rocket relations onto the server model.
     */
    public function loadRocketRelations(Server $server, bool $refresh = false): Server
    {
        if (!$server->relationLoaded('rocket') || $refresh) {
            $server->load('rocket.scriptFrom');
        }

        return $server;
    }

    /**
     * Return a collection of servers with their associated data for rebuild operations.
     */
    public function getDataForRebuild(int $server = null, int $cluster = null): Collection
    {
        $instance = $this->getBuilder()->with(['allocation', 'allocations', 'rocket', 'cluster']);

        if (!is_null($server) && is_null($cluster)) {
            $instance = $instance->where('id', '=', $server);
        } elseif (is_null($server) && !is_null($cluster)) {
            $instance = $instance->where('cluster_id', '=', $cluster);
        }

        return $instance->get($this->getColumns());
    }

    /**
     * Return a collection of servers with their associated data for reinstall operations.
     */
    public function getDataForReinstall(int $server = null, int $cluster = null): Collection
    {
        $instance = $this->getBuilder()->with(['allocation', 'allocations', 'rocket', 'cluster']);

        if (!is_null($server) && is_null($cluster)) {
            $instance = $instance->where('id', '=', $server);
        } elseif (is_null($server) && !is_null($cluster)) {
            $instance = $instance->where('cluster_id', '=', $cluster);
        }

        return $instance->get($this->getColumns());
    }

    /**
     * Return a server model and all variables associated with the server.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function findWithVariables(int $id): Server
    {
        try {
            return $this->getBuilder()->with('rocket.variables', 'variables')
                ->where($this->getModel()->getKeyName(), '=', $id)
                ->firstOrFail($this->getColumns());
        } catch (ModelNotFoundException) {
            throw new RecordNotFoundException();
        }
    }

    /**
     * Get the primary allocation for a given server. If a model is passed into
     * the function, load the allocation relationship onto it. Otherwise, find and
     * return the server from the database.
     */
    public function getPrimaryAllocation(Server $server, bool $refresh = false): Server
    {
        if (!$server->relationLoaded('allocation') || $refresh) {
            $server->load('allocation');
        }

        return $server;
    }

    /**
     * Return enough data to be used for the creation of a server via the daemon.
     */
    public function getDataForCreation(Server $server, bool $refresh = false): Server
    {
        foreach (['allocation', 'allocations', 'rocket'] as $relation) {
            if (!$server->relationLoaded($relation) || $refresh) {
                $server->load($relation);
            }
        }

        return $server;
    }

    /**
     * Load associated databases onto the server model.
     */
    public function loadDatabaseRelations(Server $server, bool $refresh = false): Server
    {
        if (!$server->relationLoaded('databases') || $refresh) {
            $server->load('databases.host');
        }

        return $server;
    }

    /**
     * Get data for use when updating a server on the Daemon. Returns an array of
     * the rocket which is used for build and rebuild. Only loads relations
     * if they are missing, or refresh is set to true.
     */
    public function getDaemonServiceData(Server $server, bool $refresh = false): array
    {
        if (!$server->relationLoaded('rocket') || $refresh) {
            $server->load('rocket');
        }

        return [
            'rocket' => $server->getRelation('rocket')->uuid,
        ];
    }

    /**
     * Return a server by UUID.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getByUuid(string $uuid): Server
    {
        try {
            /** @var \Kubectyl\Models\Server $model */
            $model = $this->getBuilder()
                ->with('launchpad', 'cluster')
                ->where(function (Builder $query) use ($uuid) {
                    $query->where('uuidShort', $uuid)->orWhere('uuid', $uuid);
                })
                ->firstOrFail($this->getColumns());

            return $model;
        } catch (ModelNotFoundException) {
            throw new RecordNotFoundException();
        }
    }

    /**
     * Check if a given UUID and UUID-Short string are unique to a server.
     */
    public function isUniqueUuidCombo(string $uuid, string $short): bool
    {
        return !$this->getBuilder()->where('uuid', '=', $uuid)->orWhere('uuidShort', '=', $short)->exists();
    }

    /**
     * Returns all the servers that exist for a given cluster in a paginated response.
     */
    public function loadAllServersForCluster(int $cluster, int $limit): LengthAwarePaginator
    {
        return $this->getBuilder()
            ->with(['user', 'launchpad', 'rocket'])
            ->where('cluster_id', '=', $cluster)
            ->paginate($limit);
    }
}
