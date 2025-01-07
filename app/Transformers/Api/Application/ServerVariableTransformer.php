<?php

namespace Kubectyl\Transformers\Api\Application;

use League\Fractal\Resource\Item;
use Kubectyl\Models\RocketVariable;
use Kubectyl\Services\Acl\Api\AdminAcl;
use League\Fractal\Resource\NullResource;

class ServerVariableTransformer extends BaseTransformer
{
    /**
     * List of resources that can be included.
     */
    protected array $availableIncludes = ['parent'];

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return ServerVariable::RESOURCE_NAME;
    }

    /**
     * Return a generic transformed server variable array.
     */
    public function transform(RocketVariable $variable): array
    {
        return $variable->toArray();
    }

    /**
     * Return the parent service variable data.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeParent(RocketVariable $variable): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_ROCKETS)) {
            return $this->null();
        }

        $variable->loadMissing('variable');

        return $this->item($variable->getRelation('variable'), $this->makeTransformer(RocketVariableTransformer::class), 'variable');
    }
}
