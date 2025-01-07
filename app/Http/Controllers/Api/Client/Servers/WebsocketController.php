<?php

namespace Kubectyl\Http\Controllers\Api\Client\Servers;

use Carbon\CarbonImmutable;
use Kubectyl\Models\Server;
use Kubectyl\Models\Permission;
use Illuminate\Http\JsonResponse;
use Kubectyl\Services\Clusters\ClusterJWTService;
use Kubectyl\Exceptions\Http\HttpForbiddenException;
use Kubectyl\Http\Requests\Api\Client\ClientApiRequest;
use Kubectyl\Services\Servers\GetUserPermissionsService;
use Kubectyl\Http\Controllers\Api\Client\ClientApiController;

class WebsocketController extends ClientApiController
{
    /**
     * WebsocketController constructor.
     */
    public function __construct(
        private ClusterJWTService $jwtService,
        private GetUserPermissionsService $permissionsService
    ) {
        parent::__construct();
    }

    /**
     * Generates a one-time token that is sent along in every websocket call to the Daemon.
     * This is a signed JWT that the Daemon then uses to verify the user's identity, and
     * allows us to continually renew this token and avoid users maintaining sessions wrongly,
     * as well as ensure that user's only perform actions they're allowed to.
     */
    public function __invoke(ClientApiRequest $request, Server $server): JsonResponse
    {
        $user = $request->user();
        if ($user->cannot(Permission::ACTION_WEBSOCKET_CONNECT, $server)) {
            throw new HttpForbiddenException('You do not have permission to connect to this server\'s websocket.');
        }

        $permissions = $this->permissionsService->handle($server, $user);

        $cluster = $server->cluster;
        if (!is_null($server->transfer)) {
            // Check if the user has permissions to receive transfer logs.
            if (!in_array('admin.websocket.transfer', $permissions)) {
                throw new HttpForbiddenException('You do not have permission to view server transfer logs.');
            }

            // Redirect the websocket request to the new cluster if the server has been archived.
            if ($server->transfer->archived) {
                $cluster = $server->transfer->newCluster;
            }
        }

        $token = $this->jwtService
            ->setExpiresAt(CarbonImmutable::now()->addMinutes(10))
            ->setUser($request->user())
            ->setClaims([
                'server_uuid' => $server->uuid,
                'permissions' => $permissions,
            ])
            ->handle($cluster, $user->id . $server->uuid);

        $socket = str_replace(['https://', 'http://'], ['wss://', 'ws://'], $cluster->getConnectionAddress());

        return new JsonResponse([
            'data' => [
                'token' => $token->toString(),
                'socket' => $socket . sprintf('/api/servers/%s/ws', $server->uuid),
            ],
        ]);
    }
}
