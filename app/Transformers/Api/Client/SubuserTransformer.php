<?php

namespace Kubectyl\Transformers\Api\Client;

use Kubectyl\Models\Subuser;

class SubuserTransformer extends BaseClientTransformer
{
    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Subuser::RESOURCE_NAME;
    }

    /**
     * Transforms a subuser into a model that can be shown to a front-end user.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function transform(Subuser $model): array
    {
        return array_merge(
            $this->makeTransformer(UserTransformer::class)->transform($model->user),
            ['permissions' => $model->permissions]
        );
    }
}
