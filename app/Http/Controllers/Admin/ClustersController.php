<?php

namespace Kubectyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Kubectyl\Models\Cluster;
use Illuminate\Http\Response;
use Kubectyl\Models\Allocation;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Cache\Repository as CacheRepository;
use Kubectyl\Services\Allocations\AssignmentService;
use Kubectyl\Services\Clusters\ClusterUpdateService;
use Kubectyl\Services\Helpers\SoftwareVersionService;
use Kubectyl\Services\Clusters\ClusterCreationService;
use Kubectyl\Services\Clusters\ClusterDeletionService;
use Kubectyl\Contracts\Repository\ServerRepositoryInterface;
use Kubectyl\Http\Requests\Admin\Cluster\ClusterFormRequest;
use Kubectyl\Services\Allocations\AllocationDeletionService;
use Kubectyl\Contracts\Repository\ClusterRepositoryInterface;
use Kubectyl\Contracts\Repository\LocationRepositoryInterface;
use Kubectyl\Http\Requests\Admin\Cluster\AllocationFormRequest;
use Kubectyl\Contracts\Repository\AllocationRepositoryInterface;
use Kubectyl\Http\Requests\Admin\Cluster\AllocationAliasFormRequest;

class ClustersController extends Controller
{
    /**
     * ClustersController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected AllocationDeletionService $allocationDeletionService,
        protected AllocationRepositoryInterface $allocationRepository,
        protected AssignmentService $assignmentService,
        protected CacheRepository $cache,
        protected ClusterCreationService $creationService,
        protected ClusterDeletionService $deletionService,
        protected LocationRepositoryInterface $locationRepository,
        protected ClusterRepositoryInterface $repository,
        protected ServerRepositoryInterface $serverRepository,
        protected ClusterUpdateService $updateService,
        protected SoftwareVersionService $versionService,
        protected ViewFactory $view
    ) {
    }

    /**
     * Displays create new cluster page.
     */
    public function create(): View|RedirectResponse
    {
        $locations = $this->locationRepository->all();
        if (count($locations) < 1) {
            $this->alert->warning(trans('admin/cluster.notices.location_required'))->flash();

            return redirect()->route('admin.locations');
        }

        return $this->view->make('admin.clusters.new', ['locations' => $locations]);
    }

    /**
     * Post controller to create a new cluster on the system.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    public function store(ClusterFormRequest $request): RedirectResponse
    {
        $cluster = $this->creationService->handle($request->normalize());
        // $this->alert->info(trans('admin/cluster.notices.cluster_created'))->flash();

        return redirect()->route('admin.clusters.view.configuration', $cluster->id);
    }

    /**
     * Updates settings for a cluster.
     *
     * @throws \Kubectyl\Exceptions\DisplayException
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function updateSettings(ClusterFormRequest $request, Cluster $cluster): RedirectResponse
    {
        $this->updateService->handle($cluster, $request->normalize(), $request->input('reset_secret') === 'on');
        $this->alert->success(trans('admin/cluster.notices.cluster_updated'))->flash();

        return redirect()->route('admin.clusters.view.settings', $cluster->id)->withInput();
    }

    /**
     * Removes a single allocation from a cluster.
     *
     * @throws \Kubectyl\Exceptions\Service\Allocation\ServerUsingAllocationException
     */
    public function allocationRemoveSingle(int $cluster, Allocation $allocation): Response
    {
        $this->allocationDeletionService->handle($allocation);

        return response('', 204);
    }

    /**
     * Removes multiple individual allocations from a cluster.
     *
     * @throws \Kubectyl\Exceptions\Service\Allocation\ServerUsingAllocationException
     */
    public function allocationRemoveMultiple(Request $request, int $cluster): Response
    {
        $allocations = $request->input('allocations');
        foreach ($allocations as $rawAllocation) {
            $allocation = new Allocation();
            $allocation->id = $rawAllocation['id'];
            $this->allocationRemoveSingle($cluster, $allocation);
        }

        return response('', 204);
    }

    /**
     * Remove all allocations for a specific IP at once on a cluster.
     */
    public function allocationRemoveBlock(Request $request, int $cluster): RedirectResponse
    {
        $this->allocationRepository->deleteWhere([
            ['cluster_id', '=', $cluster],
            ['server_id', '=', null],
            ['ip', '=', $request->input('ip')],
        ]);

        $this->alert->success(trans('admin/cluster.notices.unallocated_deleted', ['ip' => $request->input('ip')]))
            ->flash();

        return redirect()->route('admin.clusters.view.allocation', $cluster);
    }

    /**
     * Sets an alias for a specific allocation on a cluster.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function allocationSetAlias(AllocationAliasFormRequest $request): \Symfony\Component\HttpFoundation\Response
    {
        $this->allocationRepository->update($request->input('allocation_id'), [
            'ip_alias' => (empty($request->input('alias'))) ? null : $request->input('alias'),
        ]);

        return response('', 204);
    }

    /**
     * Creates new allocations on a cluster.
     *
     * @throws \Kubectyl\Exceptions\Service\Allocation\CidrOutOfRangeException
     * @throws \Kubectyl\Exceptions\Service\Allocation\InvalidPortMappingException
     * @throws \Kubectyl\Exceptions\Service\Allocation\PortOutOfRangeException
     * @throws \Kubectyl\Exceptions\Service\Allocation\TooManyPortsInRangeException
     */
    public function createAllocation(AllocationFormRequest $request, Cluster $cluster): RedirectResponse
    {
        $this->assignmentService->handle($cluster, $request->normalize());
        $this->alert->success(trans('admin/cluster.notices.allocations_added'))->flash();

        return redirect()->route('admin.clusters.view.allocation', $cluster->id);
    }

    /**
     * Deletes a cluster from the system.
     *
     * @throws \Kubectyl\Exceptions\DisplayException
     */
    public function delete(int|Cluster $cluster): RedirectResponse
    {
        $this->deletionService->handle($cluster);
        $this->alert->success(trans('admin/cluster.notices.cluster_deleted'))->flash();

        return redirect()->route('admin.clusters');
    }
}
