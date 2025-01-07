<?php

namespace Kubectyl\Http\Controllers\Admin\Clusters;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Kubectyl\Models\Cluster;
use Illuminate\Http\JsonResponse;
use Kubectyl\Http\Controllers\Controller;
use Kubectyl\Repositories\Kuber\DaemonConfigurationRepository;

class SystemInformationController extends Controller
{
    /**
     * SystemInformationController constructor.
     */
    public function __construct(private DaemonConfigurationRepository $repository)
    {
    }

    /**
     * Returns system information from the Daemon.
     *
     * @throws \Kubectyl\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function __invoke(Request $request, Cluster $cluster): JsonResponse
    {
        $data = $this->repository->setCluster($cluster)->getSystemInformation();

        return new JsonResponse([
            'version' => $data['version'] ?? 'unavailable',
            'system' => [
                'type' => Str::title($data['os'] ?? 'Unknown'),
                'arch' => $data['architecture'] ?? '--',
                'release' => $data['kernel_version'] ?? '--',
                'cpus' => $data['cpu_count'] ?? 0,
                'git' => $data['git_version'] ?? '--',
                'go' => $data['go_version'] ?? '--',
                'platform' => $data['platform'] ?? '--',
            ],
        ]);
    }
}
