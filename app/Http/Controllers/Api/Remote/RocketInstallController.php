<?php

namespace Kubectyl\Http\Controllers\Api\Remote;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Kubectyl\Http\Controllers\Controller;
use Kubectyl\Services\Servers\EnvironmentService;
use Kubectyl\Contracts\Repository\ServerRepositoryInterface;

class RocketInstallController extends Controller
{
    /**
     * RocketInstallController constructor.
     */
    public function __construct(private EnvironmentService $environment, private ServerRepositoryInterface $repository)
    {
    }

    /**
     * Handle request to get script and installation information for a server
     * that is being created on the cluster.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function index(Request $request, string $uuid): JsonResponse
    {
        $cluster = $request->attributes->get('cluster');

        /** @var \Kubectyl\Models\Server $server */
        $server = $this->repository->findFirstWhere([
            ['uuid', '=', $uuid],
            ['cluster_id', '=', $cluster->id],
        ]);

        $this->repository->loadRocketRelations($server);
        $rocket = $server->getRelation('rocket');

        return response()->json([
            'scripts' => [
                'install' => !$rocket->copy_script_install ? null : str_replace(["\r\n", "\n", "\r"], "\n", $rocket->copy_script_install),
                'privileged' => $rocket->script_is_privileged,
            ],
            'config' => [
                'container' => $rocket->copy_script_container,
                'entry' => $rocket->copy_script_entry,
            ],
            'env' => $this->environment->handle($server),
        ]);
    }
}
