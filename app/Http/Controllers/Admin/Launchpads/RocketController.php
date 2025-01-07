<?php

namespace Kubectyl\Http\Controllers\Admin\Launchpads;

use Illuminate\View\View;
use Kubectyl\Models\Rocket;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;
use Kubectyl\Services\Rockets\RocketUpdateService;
use Kubectyl\Services\Rockets\RocketCreationService;
use Kubectyl\Services\Rockets\RocketDeletionService;
use Kubectyl\Http\Requests\Admin\Rocket\RocketFormRequest;
use Kubectyl\Contracts\Repository\RocketRepositoryInterface;
use Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface;

class RocketController extends Controller
{
    /**
     * RocketController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected RocketCreationService $creationService,
        protected RocketDeletionService $deletionService,
        protected RocketRepositoryInterface $repository,
        protected RocketUpdateService $updateService,
        protected LaunchpadRepositoryInterface $launchpadRepository,
        protected ViewFactory $view
    ) {
    }

    /**
     * Handle a request to display the Rocket creation page.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function create(): View
    {
        $launchpads = $this->launchpadRepository->getWithRockets();
        \JavaScript::put(['launchpads' => $launchpads->keyBy('id')]);

        return $this->view->make('admin.rockets.new', ['launchpads' => $launchpads]);
    }

    /**
     * Handle request to store a new Rocket.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Service\Rocket\NoParentConfigurationFoundException
     */
    public function store(RocketFormRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['docker_images'] = $this->normalizeDockerImages($data['docker_images'] ?? null);
        $data['node_selectors'] = $this->normalizeNodeSelectors($data['node_selectors'] ?? null);

        $rocket = $this->creationService->handle($data);
        $this->alert->success(trans('admin/launchpads.rockets.notices.rocket_created'))->flash();

        return redirect()->route('admin.launchpads.rocket.view', $rocket->id);
    }

    /**
     * Handle request to view a single Rocket.
     */
    public function view(Rocket $rocket): View
    {
        return $this->view->make('admin.rockets.view', [
            'rocket' => $rocket,
            'images' => array_map(
                fn ($key, $value) => $key === $value ? $value : "$key|$value",
                array_keys($rocket->docker_images),
                $rocket->docker_images,
            ),
            'selectors' => !empty($rocket->node_selectors) ? array_map(
                fn ($key, $value) => $key === $value ? $value : "$key:$value",
                array_keys($rocket->node_selectors),
                $rocket->node_selectors,
            ) : [],
        ]);
    }

    /**
     * Handle request to update an Rocket.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     * @throws \Kubectyl\Exceptions\Service\Rocket\NoParentConfigurationFoundException
     */
    public function update(RocketFormRequest $request, Rocket $rocket): RedirectResponse
    {
        $data = $request->validated();
        $data['docker_images'] = $this->normalizeDockerImages($data['docker_images'] ?? null);
        $data['node_selectors'] = $this->normalizeNodeSelectors($data['node_selectors'] ?? null);

        $this->updateService->handle($rocket, $data);
        $this->alert->success(trans('admin/launchpads.rockets.notices.updated'))->flash();

        return redirect()->route('admin.launchpads.rocket.view', $rocket->id);
    }

    /**
     * Handle request to destroy an rocket.
     *
     * @throws \Kubectyl\Exceptions\Service\Rocket\HasChildrenException
     * @throws \Kubectyl\Exceptions\Service\HasActiveServersException
     */
    public function destroy(Rocket $rocket): RedirectResponse
    {
        $this->deletionService->handle($rocket->id);
        $this->alert->success(trans('admin/launchpads.rockets.notices.deleted'))->flash();

        return redirect()->route('admin.launchpads.view', $rocket->launchpad_id);
    }

    /**
     * Normalizes a string of docker image data into the expected rocket format.
     */
    protected function normalizeDockerImages(string $input = null): array
    {
        $data = array_map(fn ($value) => trim($value), explode("\n", $input ?? ''));

        $images = [];
        // Iterate over the image data provided and convert it into a name => image
        // pairing that is used to improve the display on the front-end.
        foreach ($data as $value) {
            $parts = explode('|', $value, 2);
            $images[$parts[0]] = empty($parts[1]) ? $parts[0] : $parts[1];
        }

        return $images;
    }

    /**
     * Normalizes a string of cluster selectors data into the expected rocket format.
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
