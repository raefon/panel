<?php

namespace Kubectyl\Http\Controllers\Api\Remote\Servers;

use Carbon\CarbonImmutable;
use Kubectyl\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Kubectyl\Http\Controllers\Controller;
use Kubectyl\Repositories\Eloquent\ServerRepository;
use Kubectyl\Events\Server\Installed as ServerInstalled;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Kubectyl\Http\Requests\Api\Remote\InstallationDataRequest;

class ServerInstallController extends Controller
{
    /**
     * ServerInstallController constructor.
     */
    public function __construct(private ServerRepository $repository, private EventDispatcher $eventDispatcher)
    {
    }

    /**
     * Returns installation information for a server.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function index(Request $request, string $uuid): JsonResponse
    {
        $server = $this->repository->getByUuid($uuid);
        $rocket = $server->rocket;

        return new JsonResponse([
            'container_image' => $rocket->copy_script_container,
            'entrypoint' => $rocket->copy_script_entry,
            'script' => $rocket->copy_script_install,
        ]);
    }

    /**
     * Updates the installation state of a server.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    public function store(InstallationDataRequest $request, string $uuid): JsonResponse
    {
        $server = $this->repository->getByUuid($uuid);
        $status = null;

        // Make sure the type of failure is accurate
        if (!$request->boolean('successful')) {
            $status = Server::STATUS_INSTALL_FAILED;

            if ($request->boolean('reinstall')) {
                $status = Server::STATUS_REINSTALL_FAILED;
            }
        }

        // Keep the server suspended if it's already suspended
        if ($server->status === Server::STATUS_SUSPENDED) {
            $status = Server::STATUS_SUSPENDED;
        }

        $this->repository->update($server->id, ['status' => $status, 'installed_at' => CarbonImmutable::now()], true, true);

        // If the server successfully installed, fire installed event.
        // This logic allows individually disabling install and reinstall notifications separately.
        $isInitialInstall = is_null($server->installed_at);
        if ($isInitialInstall && config()->get('kubectyl.email.send_install_notification', true)) {
            $this->eventDispatcher->dispatch(new ServerInstalled($server));
        } elseif (!$isInitialInstall && config()->get('kubectyl.email.send_reinstall_notification', true)) {
            $this->eventDispatcher->dispatch(new ServerInstalled($server));
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
