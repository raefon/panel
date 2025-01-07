<?php

namespace Kubectyl\Http\Controllers\Api\Client\Servers;

use Kubectyl\Models\Server;
use Illuminate\Http\Response;
use Kubectyl\Facades\Activity;
use Illuminate\Http\JsonResponse;
use Kubectyl\Repositories\Eloquent\ServerRepository;
use Kubectyl\Services\Servers\ReinstallServerService;
use Kubectyl\Http\Controllers\Api\Client\ClientApiController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Kubectyl\Http\Requests\Api\Client\Servers\Settings\RenameServerRequest;
use Kubectyl\Http\Requests\Api\Client\Servers\Settings\SetDockerImageRequest;
use Kubectyl\Http\Requests\Api\Client\Servers\Settings\ReinstallServerRequest;

class SettingsController extends ClientApiController
{
    /**
     * SettingsController constructor.
     */
    public function __construct(
        private ServerRepository $repository,
        private ReinstallServerService $reinstallServerService
    ) {
        parent::__construct();
    }

    /**
     * Renames a server.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function rename(RenameServerRequest $request, Server $server): JsonResponse
    {
        $name = $request->input('name');
        $description = $request->input('description') ?? '';
        $this->repository->update($server->id, [
            'name' => $name,
            'description' => $description,
        ]);

        if ($server->name !== $name) {
            Activity::event('server:settings.rename')
                ->property(['old' => $server->name, 'new' => $name])
                ->log();
        }

        if ($server->description !== $description) {
            Activity::event('server:settings.description')
                ->property(['old' => $server->description, 'new' => $description])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Reinstalls the server on the daemon.
     *
     * @throws \Throwable
     */
    public function reinstall(ReinstallServerRequest $request, Server $server): JsonResponse
    {
        $deleteFiles = $request->input('delete_files');
        $options = ['deleteFiles' => $deleteFiles];

        $this->reinstallServerService->handle($server, $options);

        Activity::event('server:reinstall')->log();

        return new JsonResponse([], Response::HTTP_ACCEPTED);
    }

    /**
     * Changes the Docker image in use by the server.
     *
     * @throws \Throwable
     */
    public function dockerImage(SetDockerImageRequest $request, Server $server): JsonResponse
    {
        if (!in_array($server->image, array_values($server->rocket->docker_images))) {
            throw new BadRequestHttpException('This server\'s Docker image has been manually set by an administrator and cannot be updated.');
        }

        $original = $server->image;
        $server->forceFill(['image' => $request->input('docker_image')])->saveOrFail();

        if ($original !== $server->image) {
            Activity::event('server:startup.image')
                ->property(['old' => $original, 'new' => $request->input('docker_image')])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
