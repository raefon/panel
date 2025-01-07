<?php

namespace Kubectyl\Http\Controllers\Admin\Launchpads;

use Illuminate\View\View;
use Kubectyl\Models\Rocket;
use Kubectyl\Models\RocketVariable;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;
use Kubectyl\Contracts\Repository\RocketRepositoryInterface;
use Kubectyl\Services\Rockets\Variables\VariableUpdateService;
use Kubectyl\Services\Rockets\Variables\VariableCreationService;
use Kubectyl\Http\Requests\Admin\Rocket\RocketVariableFormRequest;
use Kubectyl\Contracts\Repository\RocketVariableRepositoryInterface;

class RocketVariableController extends Controller
{
    /**
     * RocketVariableController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected VariableCreationService $creationService,
        protected VariableUpdateService $updateService,
        protected RocketRepositoryInterface $repository,
        protected RocketVariableRepositoryInterface $variableRepository,
        protected ViewFactory $view
    ) {
    }

    /**
     * Handle request to view the variables attached to an Rocket.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function view(int $rocket): View
    {
        $rocket = $this->repository->getWithVariables($rocket);

        return $this->view->make('admin.rockets.variables', ['rocket' => $rocket]);
    }

    /**
     * Handle a request to create a new Rocket variable.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Service\Rocket\Variable\BadValidationRuleException
     * @throws \Kubectyl\Exceptions\Service\Rocket\Variable\ReservedVariableNameException
     */
    public function store(RocketVariableFormRequest $request, Rocket $rocket): RedirectResponse
    {
        $this->creationService->handle($rocket->id, $request->normalize());
        $this->alert->success(trans('admin/launchpads.variables.notices.variable_created'))->flash();

        return redirect()->route('admin.launchpads.rocket.variables', $rocket->id);
    }

    /**
     * Handle a request to update an existing Rocket variable.
     *
     * @throws \Kubectyl\Exceptions\DisplayException
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     * @throws \Kubectyl\Exceptions\Service\Rocket\Variable\ReservedVariableNameException
     */
    public function update(RocketVariableFormRequest $request, Rocket $rocket, RocketVariable $variable): RedirectResponse
    {
        $this->updateService->handle($variable, $request->normalize());
        $this->alert->success(trans('admin/launchpads.variables.notices.variable_updated', [
            'variable' => $variable->name,
        ]))->flash();

        return redirect()->route('admin.launchpads.rocket.variables', $rocket->id);
    }

    /**
     * Handle a request to delete an existing Rocket variable from the Panel.
     */
    public function destroy(int $rocket, RocketVariable $variable): RedirectResponse
    {
        $this->variableRepository->delete($variable->id);
        $this->alert->success(trans('admin/launchpads.variables.notices.variable_deleted', [
            'variable' => $variable->name,
        ]))->flash();

        return redirect()->route('admin.launchpads.rocket.variables', $rocket);
    }
}
