<?php

namespace Kubectyl\Http\Controllers\Admin;

use Kubectyl\Models\ApiKey;
use Illuminate\Http\Request;
use Kubectyl\Models\Cluster;
use Illuminate\Http\JsonResponse;
use Kubectyl\Http\Controllers\Controller;
use Kubectyl\Services\Api\KeyCreationService;
use Illuminate\Contracts\Encryption\Encrypter;
use Kubectyl\Repositories\Eloquent\ApiKeyRepository;

class ClusterAutoDeployController extends Controller
{
    /**
     * ClusterAutoDeployController constructor.
     */
    public function __construct(
        private ApiKeyRepository $repository,
        private Encrypter $encrypter,
        private KeyCreationService $keyCreationService
    ) {
    }

    /**
     * Generates a new API key for the logged-in user with only permission to read
     * clusters, and returns that as the deployment key for a cluster.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    public function __invoke(Request $request, Cluster $cluster): JsonResponse
    {
        /** @var \Kubectyl\Models\ApiKey|null $key */
        $key = $this->repository->getApplicationKeys($request->user())
            ->filter(function (ApiKey $key) {
                foreach ($key->getAttributes() as $permission => $value) {
                    if ($permission === 'r_clusters' && $value === 1) {
                        return true;
                    }
                }

                return false;
            })
            ->first();

        // We couldn't find a key that exists for this user with only permission for
        // reading clusters. Go ahead and create it now.
        if (!$key) {
            $key = $this->keyCreationService->setKeyType(ApiKey::TYPE_APPLICATION)->handle([
                'user_id' => $request->user()->id,
                'memo' => 'Automatically generated cluster deployment key.',
                'allowed_ips' => [],
            ], ['r_clusters' => 1]);
        }

        return new JsonResponse([
            'cluster' => $cluster->id,
            'token' => $key->identifier . $this->encrypter->decrypt($key->token),
        ]);
    }
}
