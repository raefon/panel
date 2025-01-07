<?php

namespace Kubectyl\Http\Controllers\Api\Application\Clusters;

use Kubectyl\Services\Deployment\FindViableClustersService;
use Kubectyl\Transformers\Api\Application\ClusterTransformer;
use Kubectyl\Http\Controllers\Api\Application\ApplicationApiController;
use Kubectyl\Http\Requests\Api\Application\Clusters\GetDeployableClustersRequest;

class ClusterDeploymentController extends ApplicationApiController
{
    /**
     * ClusterDeploymentController constructor.
     */
    public function __construct(private FindViableClustersService $viableClustersService)
    {
        parent::__construct();
    }

    /**
     * Finds any clusters that are available using the given deployment criteria. This works
     * similarly to the server creation process, but allows you to pass the deployment object
     * to this endpoint and get back a list of all Clusters satisfying the requirements.
     *
     * @throws \Kubectyl\Exceptions\Service\Deployment\NoViableClusterException
     */
    public function __invoke(GetDeployableClustersRequest $request): array
    {
        $data = $request->validated();
        $clusters = $this->viableClustersService->setLocations($data['location_ids'] ?? [])
            ->handle($request->query('per_page'), $request->query('page'));

        return $this->fractal->collection($clusters)
            ->transformWith($this->getTransformer(ClusterTransformer::class))
            ->toArray();
    }
}
