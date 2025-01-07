<?php

namespace Kubectyl\Transformers\Api\Application;

use Kubectyl\Models\Location;
use Kubectyl\Services\Acl\Api\AdminAcl;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\NullResource;

class LocationTransformer extends BaseTransformer
{
    /**
     * List of resources that can be included.
     */
    protected array $availableIncludes = ['clusters', 'servers'];

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Location::RESOURCE_NAME;
    }

    /**
     * Return a generic transformed location array.
     */
    public function transform(Location $location): array
    {
        return [
            'id' => $location->id,
            'short' => $location->short,
            'long' => $location->long,
            $location->getUpdatedAtColumn() => $this->formatTimestamp($location->updated_at),
            $location->getCreatedAtColumn() => $this->formatTimestamp($location->created_at),
        ];
    }

    /**
     * Return the clusters associated with this location.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeServers(Location $location): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_SERVERS)) {
            return $this->null();
        }

        $location->loadMissing('servers');

        return $this->collection($location->getRelation('servers'), $this->makeTransformer(ServerTransformer::class), 'server');
    }

    /**
     * Return the clusters associated with this location.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeClusters(Location $location): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_CLUSTERS)) {
            return $this->null();
        }

        $location->loadMissing('clusters');

        return $this->collection($location->getRelation('clusters'), $this->makeTransformer(ClusterTransformer::class), 'cluster');
    }
}
