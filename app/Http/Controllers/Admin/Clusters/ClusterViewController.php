<?php

namespace Kubectyl\Http\Controllers\Admin\Clusters;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Kubectyl\Models\Cluster;
use Kubectyl\Models\Allocation;
use Illuminate\Support\Collection;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\Encrypter;
use Kubectyl\Repositories\Eloquent\ServerRepository;
use Kubectyl\Traits\Controllers\JavascriptInjection;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Kubectyl\Repositories\Eloquent\ClusterRepository;
use Kubectyl\Services\Helpers\SoftwareVersionService;
use Kubectyl\Repositories\Eloquent\LocationRepository;
use Kubectyl\Repositories\Eloquent\AllocationRepository;

class ClusterViewController extends Controller
{
    use JavascriptInjection;

    /**
     * ClusterViewController constructor.
     */
    public function __construct(
        private AllocationRepository $allocationRepository,
        private LocationRepository $locationRepository,
        private ClusterRepository $repository,
        private ServerRepository $serverRepository,
        private SoftwareVersionService $versionService,
        private ViewFactory $view
    ) {
    }

    /**
     * Returns index view for a specific cluster on the system.
     */
    public function index(Request $request, Cluster $cluster): View
    {
        $cluster = $this->repository->loadLocationAndServerCount($cluster);

        return $this->view->make('admin.clusters.view.index', [
            'cluster' => $cluster,
            'servers' => $this->serverRepository->loadAllServersForCluster($cluster->id, 25),
            'version' => $this->versionService,
        ]);
    }

    /**
     * Returns the settings page for a specific cluster.
     */
    public function settings(Request $request, Cluster $cluster): View
    {
        $cluster['bearer_token'] = app(Encrypter::class)->decrypt($cluster['bearer_token']);

        return $this->view->make('admin.clusters.view.settings', [
            'cluster' => $cluster,
            'locations' => $this->locationRepository->all(),
        ]);
    }

    /**
     * Return the configuration page for a specific cluster.
     */
    public function configuration(Request $request, Cluster $cluster): View
    {
        return $this->view->make('admin.clusters.view.configuration', compact('cluster'));
    }

    /**
     * Return the cluster allocation management page.
     */
    public function allocations(Request $request, Cluster $cluster): View
    {
        $cluster = $this->repository->loadClusterAllocations($cluster);

        $this->plainInject(['cluster' => Collection::wrap($cluster)->only(['id'])]);

        return $this->view->make('admin.clusters.view.allocation', [
            'cluster' => $cluster,
            'allocations' => Allocation::query()->where('cluster_id', $cluster->id)
                ->groupBy('ip')
                ->orderByRaw('INET_ATON(ip) ASC')
                ->get(['ip']),
        ]);
    }

    /**
     * Return a listing of servers that exist for this specific cluster.
     */
    public function servers(Request $request, Cluster $cluster): View
    {
        $this->plainInject([
            'cluster' => Collection::wrap($cluster->makeVisible(['daemon_token_id', 'daemon_token']))
                ->only(['scheme', 'fqdn', 'daemonListen', 'daemon_token_id', 'daemon_token']),
        ]);

        return $this->view->make('admin.clusters.view.servers', [
            'cluster' => $cluster,
            'servers' => $this->serverRepository->loadAllServersForCluster($cluster->id, 25),
        ]);
    }
}
