<?php

namespace Kubectyl\Services\Servers;

use Kubectyl\Models\Server;
use Illuminate\Database\ConnectionInterface;
use Kubectyl\Repositories\Kuber\DaemonServerRepository;

class ReinstallServerService
{
    /**
     * ReinstallService constructor.
     */
    public function __construct(
        private ConnectionInterface $connection,
        private DaemonServerRepository $daemonServerRepository
    ) {
    }

    /**
     * Reinstall a server on the remote daemon.
     *
     * @throws \Throwable
     */
    public function handle(Server $server, array $options = []): Server
    {
        $deleteFiles = $options['deleteFiles'] ?? true;

        return $this->connection->transaction(function () use ($server, $deleteFiles) {
            $server->fill(['status' => Server::STATUS_INSTALLING])->save();

            $this->daemonServerRepository->setServer($server)->reinstall($deleteFiles);

            return $server->refresh();
        });
    }
}
