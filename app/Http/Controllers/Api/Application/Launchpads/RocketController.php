<?php

namespace Kubectyl\Http\Controllers\Api\Application\Launchpads;

use Kubectyl\Models\Rocket;
use Kubectyl\Models\Launchpad;
use Kubectyl\Transformers\Api\Application\RocketTransformer;
use Kubectyl\Http\Controllers\Api\Application\ApplicationApiController;
use Kubectyl\Http\Requests\Api\Application\Launchpads\Rockets\GetRocketRequest;
use Kubectyl\Http\Requests\Api\Application\Launchpads\Rockets\GetRocketsRequest;

class RocketController extends ApplicationApiController
{
    /**
     * Return all rocket that exist for a given launchpad.
     */
    public function index(GetRocketsRequest $request, Launchpad $launchpad): array
    {
        return $this->fractal->collection($launchpad->rockets)
            ->transformWith($this->getTransformer(RocketTransformer::class))
            ->toArray();
    }

    /**
     * Return a single rocket that exists on the specified launchpad.
     */
    public function view(GetRocketRequest $request, Launchpad $launchpad, Rocket $rocket): array
    {
        return $this->fractal->item($rocket)
            ->transformWith($this->getTransformer(RocketTransformer::class))
            ->toArray();
    }
}
