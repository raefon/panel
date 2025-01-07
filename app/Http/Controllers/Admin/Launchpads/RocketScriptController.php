<?php

namespace Kubectyl\Http\Controllers\Admin\Launchpads;

use Illuminate\View\View;
use Kubectyl\Models\Rocket;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;
use Kubectyl\Services\Rockets\Scripts\InstallScriptService;
use Kubectyl\Contracts\Repository\RocketRepositoryInterface;
use Kubectyl\Http\Requests\Admin\Rocket\RocketScriptFormRequest;

class RocketScriptController extends Controller
{
    /**
     * RocketScriptController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected RocketRepositoryInterface $repository,
        protected InstallScriptService $installScriptService,
        protected ViewFactory $view
    ) {
    }

    /**
     * Handle requests to render installation script for an Rocket.
     */
    public function index(int $rocket): View
    {
        $rocket = $this->repository->getWithCopyAttributes($rocket);
        $copy = $this->repository->findWhere([
            ['copy_script_from', '=', null],
            ['launchpad_id', '=', $rocket->launchpad_id],
            ['id', '!=', $rocket],
        ]);

        $rely = $this->repository->findWhere([
            ['copy_script_from', '=', $rocket->id],
        ]);

        return $this->view->make('admin.rockets.scripts', [
            'copyFromOptions' => $copy,
            'relyOnScript' => $rely,
            'rocket' => $rocket,
        ]);
    }

    /**
     * Handle a request to update the installation script for an Rocket.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     * @throws \Kubectyl\Exceptions\Service\Rocket\InvalidCopyFromException
     */
    public function update(RocketScriptFormRequest $request, Rocket $rocket): RedirectResponse
    {
        $this->installScriptService->handle($rocket, $request->normalize());
        $this->alert->success(trans('admin/launchpads.rockets.notices.script_updated'))->flash();

        return redirect()->route('admin.launchpads.rocket.scripts', $rocket);
    }
}
