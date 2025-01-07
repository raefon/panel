<?php

namespace Kubectyl\Http\Controllers\Api\Application\Launchpads;

use Kubectyl\Models\Launchpad;
use Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface;
use Kubectyl\Transformers\Api\Application\LaunchpadTransformer;
use Kubectyl\Http\Controllers\Api\Application\ApplicationApiController;
use Kubectyl\Http\Requests\Api\Application\Launchpads\GetLaunchpadsRequest;

class LaunchpadController extends ApplicationApiController
{
    /**
     * LaunchpadController constructor.
     */
    public function __construct(private LaunchpadRepositoryInterface $repository)
    {
        parent::__construct();
    }

    /**
     * Return all Nests that exist on the Panel.
     */
    public function index(GetLaunchpadsRequest $request): array
    {
        $launchpads = $this->repository->paginated($request->query('per_page') ?? 50);

        return $this->fractal->collection($launchpads)
            ->transformWith($this->getTransformer(LaunchpadTransformer::class))
            ->toArray();
    }

    /**
     * Return information about a single Launchpad model.
     */
    public function view(GetLaunchpadsRequest $request, Launchpad $launchpad): array
    {
        return $this->fractal->item($launchpad)
            ->transformWith($this->getTransformer(LaunchpadTransformer::class))
            ->toArray();
    }
}
