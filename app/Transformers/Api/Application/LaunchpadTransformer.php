<?php

namespace Kubectyl\Transformers\Api\Application;

use Kubectyl\Models\Rocket;
use Kubectyl\Models\Server;
use Kubectyl\Models\Launchpad;
use Kubectyl\Services\Acl\Api\AdminAcl;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\NullResource;

class LaunchpadTransformer extends BaseTransformer
{
    /**
     * Relationships that can be loaded onto this transformation.
     */
    protected array $availableIncludes = [
        'rockets', 'servers',
    ];

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Launchpad::RESOURCE_NAME;
    }

    /**
     * Transform a Launchpad model into a representation that can be consumed by the
     * application API.
     */
    public function transform(Launchpad $model): array
    {
        $response = $model->toArray();

        $response[$model->getUpdatedAtColumn()] = $this->formatTimestamp($model->updated_at);
        $response[$model->getCreatedAtColumn()] = $this->formatTimestamp($model->created_at);

        return $response;
    }

    /**
     * Include the Rockets relationship on the given Launchpad model transformation.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeRockets(Launchpad $model): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_ROCKETS)) {
            return $this->null();
        }

        $model->loadMissing('rockets');

        return $this->collection($model->getRelation('rockets'), $this->makeTransformer(RocketTransformer::class), Rocket::RESOURCE_NAME);
    }

    /**
     * Include the servers relationship on the given Launchpad model.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeServers(Launchpad $model): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_SERVERS)) {
            return $this->null();
        }

        $model->loadMissing('servers');

        return $this->collection($model->getRelation('servers'), $this->makeTransformer(ServerTransformer::class), Server::RESOURCE_NAME);
    }
}
