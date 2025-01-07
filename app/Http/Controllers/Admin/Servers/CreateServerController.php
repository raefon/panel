<?php

namespace Kubectyl\Http\Controllers\Admin\Servers;

use Illuminate\View\View;
use Kubectyl\Models\Cluster;
use Kubectyl\Models\Location;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;
use Kubectyl\Http\Requests\Admin\ServerFormRequest;
use Kubectyl\Repositories\Eloquent\ServerRepository;
use Kubectyl\Services\Servers\ServerCreationService;
use Kubectyl\Repositories\Eloquent\ClusterRepository;
use Kubectyl\Repositories\Eloquent\LaunchpadRepository;

class CreateServerController extends Controller
{
    /**
     * CreateServerController constructor.
     */
    public function __construct(
        private AlertsMessageBag $alert,
        private LaunchpadRepository $launchpadRepository,
        private ServerRepository $serverRepository,
        private ClusterRepository $clusterRepository,
        private ServerCreationService $creationService,
        private ViewFactory $view
    ) {
    }

    /**
     * Displays the create server page.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function index(): View|RedirectResponse
    {
        $clusters = Cluster::all();
        if (count($clusters) < 1) {
            $this->alert->warning(trans('admin/server.alerts.cluster_required'))->flash();

            return redirect()->route('admin.clusters');
        }

        $launchpads = $this->launchpadRepository->getWithRockets();

        \JavaScript::put([
            'clusterData' => $this->clusterRepository->getClustersForServerCreation(),
            'launchpads' => $launchpads->map(function ($item) {
                return array_merge($item->toArray(), [
                    'rockets' => $item->rockets->keyBy('id')->toArray(),
                ]);
            })->keyBy('id'),
        ]);

        return $this->view->make('admin.servers.new', [
            'locations' => Location::all(),
            'launchpads' => $launchpads,
        ]);
    }

    /**
     * Create a new server on the remote system.
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Kubectyl\Exceptions\DisplayException
     * @throws \Kubectyl\Exceptions\Service\Deployment\NoViableAllocationException
     * @throws \Kubectyl\Exceptions\Service\Deployment\NoViableClusterException
     * @throws \Throwable
     */
    public function store(ServerFormRequest $request): RedirectResponse
    {
        $data = $request->except(['_token']);
        if (!empty($data['custom_image'])) {
            $data['image'] = $data['custom_image'];
            unset($data['custom_image']);
        }

        $data['node_selectors'] = $data['node_selectors'] ? $this->normalizeNodeSelectors($data['node_selectors']) : null;

        $server = $this->creationService->handle($data);

        $this->alert->success(trans('admin/server.alerts.server_created'))->flash();

        return new RedirectResponse('/admin/servers/view/' . $server->id);
    }

    /**
     * Normalizes a string of node selectors data into the expected rocket format.
     */
    protected function normalizeNodeSelectors(string $input = null): array
    {
        $data = array_map(fn ($value) => trim($value), explode("\n", $input ?? ''));

        $images = [];
        // Iterate over the selector data provided and convert it into a key => value
        // pairing that is used to improve the display on the front-end.
        foreach ($data as $value) {
            $parts = explode(':', $value, 2);
            $images[$parts[0]] = empty($parts[1]) ? $parts[0] : $parts[1];
        }

        return $images;
    }
}
