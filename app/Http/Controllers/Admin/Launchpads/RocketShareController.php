<?php

namespace Kubectyl\Http\Controllers\Admin\Launchpads;

use Kubectyl\Models\Rocket;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Kubectyl\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Kubectyl\Services\Rockets\Sharing\RocketExporterService;
use Kubectyl\Services\Rockets\Sharing\RocketImporterService;
use Kubectyl\Http\Requests\Admin\Rocket\RocketImportFormRequest;
use Kubectyl\Services\Rockets\Sharing\RocketUpdateImporterService;

class RocketShareController extends Controller
{
    /**
     * RocketShareController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected RocketExporterService $exporterService,
        protected RocketImporterService $importerService,
        protected RocketUpdateImporterService $updateImporterService
    ) {
    }

    /**
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function export(Rocket $rocket): Response
    {
        $filename = trim(preg_replace('/\W/', '-', kebab_case($rocket->name)), '-');

        return response($this->exporterService->handle($rocket->id), 200, [
            'Content-Transfer-Encoding' => 'binary',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename=rocket-' . $filename . '.json',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Import a new service option using an XML file.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     * @throws \Kubectyl\Exceptions\Service\Rocket\BadJsonFormatException
     * @throws \Kubectyl\Exceptions\Service\InvalidFileUploadException
     */
    public function import(RocketImportFormRequest $request): RedirectResponse
    {
        $rocket = $this->importerService->handle($request->file('import_file'), $request->input('import_to_launchpad'));
        $this->alert->success(trans('admin/launchpads.rockets.notices.imported'))->flash();

        return redirect()->route('admin.launchpads.rocket.view', ['rocket' => $rocket->id]);
    }

    /**
     * Update an existing Rocket using a new imported file.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     * @throws \Kubectyl\Exceptions\Service\Rocket\BadJsonFormatException
     * @throws \Kubectyl\Exceptions\Service\InvalidFileUploadException
     */
    public function update(RocketImportFormRequest $request, Rocket $rocket): RedirectResponse
    {
        $this->updateImporterService->handle($rocket, $request->file('import_file'));
        $this->alert->success(trans('admin/launchpads.rockets.notices.updated_via_import'))->flash();

        return redirect()->route('admin.launchpads.rocket.view', ['rocket' => $rocket]);
    }
}
