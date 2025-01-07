<?php

namespace Kubectyl\Http\Controllers\Admin;

use Ramsey\Uuid\Uuid;
use Illuminate\View\View;
use Kubectyl\Models\Mount;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kubectyl\Models\Location;
use Kubectyl\Models\Launchpad;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;
use Kubectyl\Http\Requests\Admin\MountFormRequest;
use Kubectyl\Repositories\Eloquent\MountRepository;
use Kubectyl\Contracts\Repository\LocationRepositoryInterface;
use Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface;

class MountController extends Controller
{
    /**
     * MountController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected LaunchpadRepositoryInterface $launchpadRepository,
        protected LocationRepositoryInterface $locationRepository,
        protected MountRepository $repository,
        protected ViewFactory $view
    ) {
    }

    /**
     * Return the mount overview page.
     */
    public function index(): View
    {
        return $this->view->make('admin.mounts.index', [
            'mounts' => $this->repository->getAllWithDetails(),
        ]);
    }

    /**
     * Return the mount view page.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function view(string $id): View
    {
        $launchpads = Launchpad::query()->with('rockets')->get();
        $locations = Location::query()->with('clusters')->get();

        return $this->view->make('admin.mounts.view', [
            'mount' => $this->repository->getWithRelations($id),
            'launchpads' => $launchpads,
            'locations' => $locations,
        ]);
    }

    /**
     * Handle request to create new mount.
     *
     * @throws \Throwable
     */
    public function create(MountFormRequest $request): RedirectResponse
    {
        $model = (new Mount())->fill($request->validated());
        $model->forceFill(['uuid' => Uuid::uuid4()->toString()]);

        $model->saveOrFail();
        $mount = $model->fresh();

        $this->alert->success('Mount was created successfully.')->flash();

        return redirect()->route('admin.mounts.view', $mount->id);
    }

    /**
     * Handle request to update or delete location.
     *
     * @throws \Throwable
     */
    public function update(MountFormRequest $request, Mount $mount): RedirectResponse
    {
        if ($request->input('action') === 'delete') {
            return $this->delete($mount);
        }

        $mount->forceFill($request->validated())->save();

        $this->alert->success('Mount was updated successfully.')->flash();

        return redirect()->route('admin.mounts.view', $mount->id);
    }

    /**
     * Delete a location from the system.
     *
     * @throws \Exception
     */
    public function delete(Mount $mount): RedirectResponse
    {
        $mount->delete();

        return redirect()->route('admin.mounts');
    }

    /**
     * Adds rockets to the mount's many-to-many relation.
     */
    public function addRockets(Request $request, Mount $mount): RedirectResponse
    {
        $validatedData = $request->validate([
            'rockets' => 'required|exists:rockets,id',
        ]);

        $rockets = $validatedData['rockets'] ?? [];
        if (count($rockets) > 0) {
            $mount->rockets()->attach($rockets);
        }

        $this->alert->success('Mount was updated successfully.')->flash();

        return redirect()->route('admin.mounts.view', $mount->id);
    }

    /**
     * Adds clusters to the mount's many-to-many relation.
     */
    public function addClusters(Request $request, Mount $mount): RedirectResponse
    {
        $data = $request->validate(['clusters' => 'required|exists:clusters,id']);

        $clusters = $data['clusters'] ?? [];
        if (count($clusters) > 0) {
            $mount->clusters()->attach($clusters);
        }

        $this->alert->success('Mount was updated successfully.')->flash();

        return redirect()->route('admin.mounts.view', $mount->id);
    }

    /**
     * Deletes an rocket from the mount's many-to-many relation.
     */
    public function deleteRocket(Mount $mount, int $rocket_id): Response
    {
        $mount->rocket()->detach($rocket_id);

        return response('', 204);
    }

    /**
     * Deletes a cluster from the mount's many-to-many relation.
     */
    public function deleteCluster(Mount $mount, int $cluster_id): Response
    {
        $mount->clusters()->detach($cluster_id);

        return response('', 204);
    }
}
