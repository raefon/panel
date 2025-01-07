<?php

namespace Kubectyl\Services\Databases;

use Kubectyl\Models\Server;
use Webmozart\Assert\Assert;
use Kubectyl\Models\Database;
use Kubectyl\Models\DatabaseHost;
use Kubectyl\Exceptions\Service\Database\NoSuitableDatabaseHostException;

class DeployServerDatabaseService
{
    /**
     * DeployServerDatabaseService constructor.
     */
    public function __construct(private DatabaseManagementService $managementService)
    {
    }

    /**
     * @throws \Throwable
     * @throws \Kubectyl\Exceptions\Service\Database\TooManyDatabasesException
     * @throws \Kubectyl\Exceptions\Service\Database\DatabaseClientFeatureNotEnabledException
     */
    public function handle(Server $server, array $data): Database
    {
        Assert::notEmpty($data['database'] ?? null);
        Assert::notEmpty($data['remote'] ?? null);

        $hosts = DatabaseHost::query()->get()->toBase();
        if ($hosts->isEmpty()) {
            throw new NoSuitableDatabaseHostException();
        } else {
            $clusterHosts = $hosts->where('cluster_id', $server->cluster_id)->toBase();

            if ($clusterHosts->isEmpty() && !config('kubectyl.client_features.databases.allow_random')) {
                throw new NoSuitableDatabaseHostException();
            }
        }

        return $this->managementService->create($server, [
            'database_host_id' => $clusterHosts->isEmpty()
                ? $hosts->random()->id
                : $clusterHosts->random()->id,
            'database' => DatabaseManagementService::generateUniqueDatabaseName($data['database'], $server->id),
            'remote' => $data['remote'],
        ]);
    }
}
