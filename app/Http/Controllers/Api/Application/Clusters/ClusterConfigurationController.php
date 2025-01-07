<?php

namespace Kubectyl\Http\Controllers\Api\Application\Clusters;

use Kubectyl\Models\Cluster;
use Illuminate\Http\JsonResponse;
use Kubectyl\Http\Requests\Api\Application\Clusters\GetClusterRequest;
use Kubectyl\Http\Controllers\Api\Application\ApplicationApiController;

class ClusterConfigurationController extends ApplicationApiController
{
    /**
     * Returns the configuration information for a cluster. This allows for automated deployments
     * to remote machines so long as an API key is provided to the machine to make the request
     * with, and the cluster is known.
     */
    public function __invoke(GetClusterRequest $request, Cluster $cluster): JsonResponse
    {
        return new JsonResponse($cluster->getConfiguration());
    }
}
