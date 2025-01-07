<?php

namespace Kubectyl\Http\Controllers\Api\Application\Servers;

use Kubectyl\Models\Server;
use Illuminate\Http\Response;
use Kubectyl\Services\Servers\SuspensionService;
use Kubectyl\Services\Servers\ReinstallServerService;
use Kubectyl\Http\Requests\Api\Application\Servers\ServerWriteRequest;
use Kubectyl\Http\Controllers\Api\Application\ApplicationApiController;

class ServerManagementController extends ApplicationApiController
{
    /**
     * ServerManagementController constructor.
     */
    public function __construct(
        private ReinstallServerService $reinstallServerService,
        private SuspensionService $suspensionService
    ) {
        parent::__construct();
    }

    /**
     * Suspend a server on the Panel.
     *
     * @throws \Throwable
     */
    public function suspend(ServerWriteRequest $request, Server $server): Response
    {
        $this->suspensionService->toggle($server);

        return $this->returnNoContent();
    }

    /**
     * Unsuspend a server on the Panel.
     *
     * @throws \Throwable
     */
    public function unsuspend(ServerWriteRequest $request, Server $server): Response
    {
        $this->suspensionService->toggle($server, SuspensionService::ACTION_UNSUSPEND);

        return $this->returnNoContent();
    }

    /**
     * Mark a server as needing to be reinstalled.
     *
     * @throws \Kubectyl\Exceptions\DisplayException
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function reinstall(ServerWriteRequest $request, Server $server, bool $deleteFiles): Response
    {
        $this->reinstallServerService->handle($server, $deleteFiles);

        return $this->returnNoContent();
    }
}
