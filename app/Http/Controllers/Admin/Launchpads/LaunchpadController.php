<?php

namespace Kubectyl\Http\Controllers\Admin\Launchpads;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;
use Kubectyl\Services\Launchpads\LaunchpadUpdateService;
use Kubectyl\Services\Launchpads\LaunchpadCreationService;
use Kubectyl\Services\Launchpads\LaunchpadDeletionService;
use Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface;
use Kubectyl\Http\Requests\Admin\Launchpad\StoreLaunchpadFormRequest;

class LaunchpadController extends Controller
{
    /**
     * LaunchpadController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected LaunchpadCreationService $nestCreationService,
        protected LaunchpadDeletionService $nestDeletionService,
        protected LaunchpadRepositoryInterface $repository,
        protected LaunchpadUpdateService $nestUpdateService,
        protected ViewFactory $view
    ) {
    }

    /**
     * Render launchpad listing page.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function index(): View
    {
        return $this->view->make('admin.launchpads.index', [
            'launchpads' => $this->repository->getWithCounts(),
        ]);
    }

    /**
     * Render launchpad creation page.
     */
    public function create(): View
    {
        return $this->view->make('admin.launchpads.new');
    }

    /**
     * Handle the storage of a new launchpad.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    public function store(StoreLaunchpadFormRequest $request): RedirectResponse
    {
        $launchpad = $this->nestCreationService->handle($request->normalize());
        $this->alert->success(trans('admin/launchpads.notices.created', ['name' => $launchpad->name]))->flash();

        return redirect()->route('admin.launchpads.view', $launchpad->id);
    }

    /**
     * Return details about a launchpad including all the rockets and servers per rocket.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function view(int $launchpad): View
    {
        return $this->view->make('admin.launchpads.view', [
            'launchpad' => $this->repository->getWithRocketServers($launchpad),
        ]);
    }

    /**
     * Handle request to update a launchpad.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function update(StoreLaunchpadFormRequest $request, int $launchpad): RedirectResponse
    {
        $this->nestUpdateService->handle($launchpad, $request->normalize());
        $this->alert->success(trans('admin/launchpads.notices.updated'))->flash();

        return redirect()->route('admin.launchpads.view', $launchpad);
    }

    /**
     * Handle request to delete a launchpad.
     *
     * @throws \Kubectyl\Exceptions\Service\HasActiveServersException
     */
    public function destroy(int $launchpad): RedirectResponse
    {
        $this->nestDeletionService->handle($launchpad);
        $this->alert->success(trans('admin/launchpads.notices.deleted'))->flash();

        return redirect()->route('admin.launchpads');
    }
}
