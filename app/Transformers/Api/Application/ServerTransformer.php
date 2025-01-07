<?php

namespace Kubectyl\Transformers\Api\Application;

use Kubectyl\Models\Server;
use League\Fractal\Resource\Item;
use Kubectyl\Services\Acl\Api\AdminAcl;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\NullResource;
use Kubectyl\Services\Servers\EnvironmentService;

class ServerTransformer extends BaseTransformer
{
    private EnvironmentService $environmentService;

    /**
     * List of resources that can be included.
     */
    protected array $availableIncludes = [
        'allocations',
        'user',
        'subusers',
        'launchpad',
        'rocket',
        'variables',
        'location',
        'cluster',
        'databases',
        'transfer',
    ];

    /**
     * Perform dependency injection.
     */
    public function handle(EnvironmentService $environmentService)
    {
        $this->environmentService = $environmentService;
    }

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Server::RESOURCE_NAME;
    }

    /**
     * Return a generic transformed server array.
     */
    public function transform(Server $server): array
    {
        return [
            'id' => $server->getKey(),
            'external_id' => $server->external_id,
            'uuid' => $server->uuid,
            'identifier' => $server->uuidShort,
            'name' => $server->name,
            'description' => $server->description,
            'status' => $server->status,
            // This field is deprecated, please use "status".
            'suspended' => $server->isSuspended(),
            'limits' => [
                'memory' => $server->memory,
                'disk' => $server->disk,
                'cpu_request' => $server->cpu_request,
                'cpu_limit' => $server->cpu_limit,
            ],
            'feature_limits' => [
                'databases' => $server->database_limit,
                'allocations' => $server->allocation_limit,
                'snapshots' => $server->snapshot_limit,
            ],
            'user' => $server->owner_id,
            'cluster' => $server->cluster_id,
            'allocation' => $server->allocation_id,
            'launchpad' => $server->launchpad_id,
            'rocket' => $server->rocket_id,
            'container' => [
                'startup_command' => $server->startup,
                'image' => $server->image,
                // This field is deprecated, please use "status".
                'installed' => $server->isInstalled() ? 1 : 0,
                'environment' => $this->environmentService->handle($server),
            ],
            $server->getUpdatedAtColumn() => $this->formatTimestamp($server->updated_at),
            $server->getCreatedAtColumn() => $this->formatTimestamp($server->created_at),
        ];
    }

    /**
     * Return a generic array of allocations for this server.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeAllocations(Server $server): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_ALLOCATIONS)) {
            return $this->null();
        }

        $server->loadMissing('allocations');

        return $this->collection($server->getRelation('allocations'), $this->makeTransformer(AllocationTransformer::class), 'allocation');
    }

    /**
     * Return a generic array of data about subusers for this server.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeSubusers(Server $server): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_USERS)) {
            return $this->null();
        }

        $server->loadMissing('subusers');

        return $this->collection($server->getRelation('subusers'), $this->makeTransformer(SubuserTransformer::class), 'subuser');
    }

    /**
     * Return a generic array of data about subusers for this server.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeUser(Server $server): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_USERS)) {
            return $this->null();
        }

        $server->loadMissing('user');

        return $this->item($server->getRelation('user'), $this->makeTransformer(UserTransformer::class), 'user');
    }

    /**
     * Return a generic array with launchpad information for this server.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeLaunchpad(Server $server): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_LAUNCHPADS)) {
            return $this->null();
        }

        $server->loadMissing('launchpad');

        return $this->item($server->getRelation('launchpad'), $this->makeTransformer(LaunchpadTransformer::class), 'launchpad');
    }

    /**
     * Return a generic array with rocket information for this server.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeRocket(Server $server): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_ROCKETS)) {
            return $this->null();
        }

        $server->loadMissing('rocket');

        return $this->item($server->getRelation('rocket'), $this->makeTransformer(RocketTransformer::class), 'rocket');
    }

    /**
     * Return a generic array of data about subusers for this server.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeVariables(Server $server): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_SERVERS)) {
            return $this->null();
        }

        $server->loadMissing('variables');

        return $this->collection($server->getRelation('variables'), $this->makeTransformer(ServerVariableTransformer::class), 'server_variable');
    }

    /**
     * Return a generic array with location information for this server.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeLocation(Server $server): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_LOCATIONS)) {
            return $this->null();
        }

        $server->loadMissing('location');

        return $this->item($server->getRelation('location'), $this->makeTransformer(LocationTransformer::class), 'location');
    }

    /**
     * Return a generic array with cluster information for this server.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeCluster(Server $server): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_CLUSTERS)) {
            return $this->null();
        }

        $server->loadMissing('cluster');

        return $this->item($server->getRelation('cluster'), $this->makeTransformer(ClusterTransformer::class), 'cluster');
    }

    /**
     * Return a generic array with database information for this server.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeDatabases(Server $server): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_SERVER_DATABASES)) {
            return $this->null();
        }

        $server->loadMissing('databases');

        return $this->collection($server->getRelation('databases'), $this->makeTransformer(ServerDatabaseTransformer::class), 'databases');
    }
}
