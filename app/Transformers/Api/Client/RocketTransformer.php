<?php

namespace Kubectyl\Transformers\Api\Client;

use Kubectyl\Models\Rocket;

class RocketTransformer extends BaseClientTransformer
{
    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Rocket::RESOURCE_NAME;
    }

    public function transform(Rocket $rocket): array
    {
        return [
            'uuid' => $rocket->uuid,
            'name' => $rocket->name,
        ];
    }
}
