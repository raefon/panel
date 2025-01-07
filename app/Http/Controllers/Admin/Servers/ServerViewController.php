<?php

namespace Kubectyl\Http\Controllers\Admin\Servers;

use Illuminate\View\View;
use Kubectyl\Models\Server;
use Illuminate\Http\Request;
use Kubectyl\Models\Launchpad;
use Kubectyl\Exceptions\DisplayException;
use Kubectyl\Http\Controllers\Controller;
use Kubectyl\Services\Servers\EnvironmentService;
use Kubectyl\Repositories\Eloquent\MountRepository;
use Kubectyl\Repositories\Eloquent\ServerRepository;
use Kubectyl\Traits\Controllers\JavascriptInjection;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Kubectyl\Repositories\Eloquent\ClusterRepository;
use Kubectyl\Repositories\Eloquent\LocationRepository;
use Kubectyl\Repositories\Eloquent\LaunchpadRepository;
use Kubectyl\Repositories\Eloquent\DatabaseHostRepository;

class ServerViewController extends Controller
{
    use JavascriptInjection;

    /**
     * ServerViewController constructor.
     */
    public function __construct(
        private DatabaseHostRepository $databaseHostRepository,
        private LocationRepository $locationRepository,
        private MountRepository $mountRepository,
        private LaunchpadRepository $launchpadRepository,
        private ClusterRepository $clusterRepository,
        private ServerRepository $repository,
        private EnvironmentService $environmentService,
        private ViewFactory $view
    ) {
    }

    /**
     * Returns the index view for a server.
     */
    public function index(Request $request, Server $server): View
    {
        return $this->view->make('admin.servers.view.index', compact('server'));
    }

    /**
     * Returns the server details page.
     */
    public function details(Request $request, Server $server): View
    {
        return $this->view->make('admin.servers.view.details', compact('server'));
    }

    /**
     * Returns a view of server build settings.
     */
    public function build(Request $request, Server $server): View
    {
        $allocations = $server->cluster->allocations->toBase();

        return $this->view->make('admin.servers.view.build', [
            'server' => $server,
            'ports' => $server->additional_ports,
            'assigned' => $allocations->where('server_id', $server->id)->sortBy('port')->sortBy('ip'),
            'unassigned' => $allocations->where('server_id', null)->sortBy('port')->sortBy('ip'),
            'selectors' => !empty($server->node_selectors) ? array_map(
                fn ($key, $value) => $key === $value ? $value : "$key:$value",
                array_keys($server->node_selectors),
                $server->node_selectors,
            ) : [],
        ]);
    }

    /**
     * Returns the server startup management page.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function startup(Request $request, Server $server): View
    {
        $launchpads = $this->launchpadRepository->getWithRockets();
        $variables = $this->environmentService->handle($server);

        $this->plainInject([
            'server' => $server,
            'server_variables' => $variables,
            'launchpads' => $launchpads->map(function (Launchpad $item) {
                return array_merge($item->toArray(), [
                    'rockets' => $item->rockets->keyBy('id')->toArray(),
                ]);
            })->keyBy('id'),
        ]);

        return $this->view->make('admin.servers.view.startup', compact('server', 'launchpads'));
    }

    /**
     * Returns all the databases that exist for the server.
     */
    public function database(Request $request, Server $server): View
    {
        return $this->view->make('admin.servers.view.database', [
            'hosts' => $this->databaseHostRepository->all(),
            'server' => $server,
        ]);
    }

    /**
     * Returns all the mounts that exist for the server.
     */
    public function mounts(Request $request, Server $server): View
    {
        $server->load('mounts');

        return $this->view->make('admin.servers.view.mounts', [
            'mounts' => $this->mountRepository->getMountListForServer($server),
            'server' => $server,
        ]);
    }

    /**
     * Returns the base server management page, or an exception if the server
     * is in a state that cannot be recovered from.
     *
     * @throws \Kubectyl\Exceptions\DisplayException
     */
    public function manage(Request $request, Server $server): View
    {
        if ($server->status === Server::STATUS_INSTALL_FAILED) {
            throw new DisplayException('This server is in a failed install state and cannot be recovered. Please delete and re-create the server.');
        }

        // Check if the panel doesn't have at least 2 clusters configured.
        $clusters = $this->clusterRepository->all();
        $canTransfer = false;
        if (count($clusters) >= 2) {
            $canTransfer = true;
        }

        \JavaScript::put([
            'clusterData' => $this->clusterRepository->getClustersForServerCreation(),
        ]);

        return $this->view->make('admin.servers.view.manage', [
            'server' => $server,
            'locations' => $this->locationRepository->all(),
            'canTransfer' => $canTransfer,
        ]);
    }

    /**
     * Returns the server deletion page.
     */
    public function delete(Request $request, Server $server): View
    {
        return $this->view->make('admin.servers.view.delete', compact('server'));
    }
}
