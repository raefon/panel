<?php

namespace Kubectyl\Http\Controllers\Api\Client\Servers;

use Kubectyl\Models\User;
use Carbon\CarbonImmutable;
use Kubectyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Kubectyl\Services\Clusters\ClusterJWTService;
use Kubectyl\Http\Controllers\Api\Client\ClientApiController;
use Kubectyl\Http\Requests\Api\Client\Servers\Files\UploadFileRequest;

class FileUploadController extends ClientApiController
{
    /**
     * FileUploadController constructor.
     */
    public function __construct(
        private ClusterJWTService $jwtService
    ) {
        parent::__construct();
    }

    /**
     * Returns an url where files can be uploaded to.
     */
    public function __invoke(UploadFileRequest $request, Server $server): JsonResponse
    {
        return new JsonResponse([
            'object' => 'signed_url',
            'attributes' => [
                'url' => $this->getUploadUrl($server, $request->user()),
            ],
        ]);
    }

    /**
     * Returns an url where files can be uploaded to.
     */
    protected function getUploadUrl(Server $server, User $user): string
    {
        $token = $this->jwtService
            ->setExpiresAt(CarbonImmutable::now()->addMinutes(15))
            ->setUser($user)
            ->setClaims(['server_uuid' => $server->uuid])
            ->handle($server->cluster, $user->id . $server->uuid);

        return sprintf(
            '%s/upload/file?token=%s',
            $server->cluster->getConnectionAddress(),
            $token->toString()
        );
    }
}
