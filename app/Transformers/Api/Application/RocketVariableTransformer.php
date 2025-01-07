<?php

namespace Kubectyl\Transformers\Api\Application;

use Kubectyl\Models\Rocket;
use Kubectyl\Models\RocketVariable;

class RocketVariableTransformer extends BaseTransformer
{
    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Rocket::RESOURCE_NAME;
    }

    public function transform(RocketVariable $model)
    {
        return $model->toArray();
    }
}
