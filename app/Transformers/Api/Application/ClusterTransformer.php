<?php

namespace Kubectyl\Transformers\Api\Application;

use Kubectyl\Models\Cluster;
use League\Fractal\Resource\Item;
use Kubectyl\Services\Acl\Api\AdminAcl;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\NullResource;

class ClusterTransformer extends BaseTransformer
{
    /**
     * List of resources that can be included.
     */
    protected array $availableIncludes = ['allocations', 'location', 'servers'];

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Cluster::RESOURCE_NAME;
    }

    /**
     * Return a cluster transformed into a format that can be consumed by the
     * external administrative API.
     */
    public function transform(Cluster $cluster): array
    {
        $response = collect($cluster->toArray())->mapWithKeys(function ($value, $key) {
            // I messed up early in 2016 when I named this column as poorly
            // as I did. This is the tragic result of my mistakes.
            $key = ($key === 'daemonSFTP') ? 'daemonSftp' : $key;

            return [snake_case($key) => $value];
        })->toArray();

        $response[$cluster->getUpdatedAtColumn()] = $this->formatTimestamp($cluster->updated_at);
        $response[$cluster->getCreatedAtColumn()] = $this->formatTimestamp($cluster->created_at);

        return $response;
    }

    /**
     * Return the clusters associated with this location.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeAllocations(Cluster $cluster): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_ALLOCATIONS)) {
            return $this->null();
        }

        $cluster->loadMissing('allocations');

        return $this->collection(
            $cluster->getRelation('allocations'),
            $this->makeTransformer(AllocationTransformer::class),
            'allocation'
        );
    }

    /**
     * Return the clusters associated with this location.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeLocation(Cluster $cluster): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_LOCATIONS)) {
            return $this->null();
        }

        $cluster->loadMissing('location');

        return $this->item(
            $cluster->getRelation('location'),
            $this->makeTransformer(LocationTransformer::class),
            'location'
        );
    }

    /**
     * Return the clusters associated with this location.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeServers(Cluster $cluster): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_SERVERS)) {
            return $this->null();
        }

        $cluster->loadMissing('servers');

        return $this->collection(
            $cluster->getRelation('servers'),
            $this->makeTransformer(ServerTransformer::class),
            'server'
        );
    }
}
