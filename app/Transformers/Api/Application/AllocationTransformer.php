<?php

namespace Kubectyl\Transformers\Api\Application;

use Kubectyl\Models\Server;
use Kubectyl\Models\Cluster;
use Kubectyl\Models\Allocation;
use League\Fractal\Resource\Item;
use Kubectyl\Services\Acl\Api\AdminAcl;
use League\Fractal\Resource\NullResource;

class AllocationTransformer extends BaseTransformer
{
    /**
     * Relationships that can be loaded onto allocation transformations.
     */
    protected array $availableIncludes = ['cluster', 'server'];

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Allocation::RESOURCE_NAME;
    }

    /**
     * Return a generic transformed allocation array.
     */
    public function transform(Allocation $allocation): array
    {
        return [
            'id' => $allocation->id,
            'ip' => $allocation->ip,
            'alias' => $allocation->ip_alias,
            'port' => $allocation->port,
            'notes' => $allocation->notes,
            'assigned' => !is_null($allocation->server_id),
        ];
    }

    /**
     * Load the cluster relationship onto a given transformation.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeCluster(Allocation $allocation): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_CLUSTERS)) {
            return $this->null();
        }

        return $this->item(
            $allocation->cluster,
            $this->makeTransformer(ClusterTransformer::class),
            Cluster::RESOURCE_NAME
        );
    }

    /**
     * Load the server relationship onto a given transformation.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeServer(Allocation $allocation): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_SERVERS) || !$allocation->server) {
            return $this->null();
        }

        return $this->item(
            $allocation->server,
            $this->makeTransformer(ServerTransformer::class),
            Server::RESOURCE_NAME
        );
    }
}
