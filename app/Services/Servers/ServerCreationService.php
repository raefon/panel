<?php

namespace Kubectyl\Services\Servers;

use Ramsey\Uuid\Uuid;
use Kubectyl\Models\User;
use Illuminate\Support\Arr;
use Kubectyl\Models\Rocket;
use Kubectyl\Models\Server;
use Webmozart\Assert\Assert;
use Kubectyl\Models\Allocation;
use Illuminate\Support\Collection;
use Illuminate\Database\ConnectionInterface;
use Kubectyl\Models\Objects\DeploymentObject;
use Kubectyl\Repositories\Eloquent\ServerRepository;
use Kubectyl\Repositories\Kuber\DaemonServerRepository;
use Kubectyl\Services\Deployment\FindViableClustersService;
use Kubectyl\Repositories\Eloquent\ServerVariableRepository;
use Kubectyl\Services\Deployment\AllocationSelectionService;
use Kubectyl\Exceptions\Http\Connection\DaemonConnectionException;

class ServerCreationService
{
    /**
     * ServerCreationService constructor.
     */
    public function __construct(
        private AllocationSelectionService $allocationSelectionService,
        private ConnectionInterface $connection,
        private DaemonServerRepository $daemonServerRepository,
        private FindViableClustersService $findViableClustersService,
        private ServerRepository $repository,
        private ServerDeletionService $serverDeletionService,
        private ServerVariableRepository $serverVariableRepository,
        private VariableValidatorService $validatorService
    ) {
    }

    /**
     * Create a server on the Panel and trigger a request to the Daemon to begin the server
     * creation process. This function will attempt to set as many additional values
     * as possible given the input data. For example, if an allocation_id is passed with
     * no cluster_id the cluster_is will be picked from the allocation.
     *
     * @throws \Throwable
     * @throws \Kubectyl\Exceptions\DisplayException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     * @throws \Kubectyl\Exceptions\Service\Deployment\NoViableClusterException
     * @throws \Kubectyl\Exceptions\Service\Deployment\NoViableAllocationException
     */
    public function handle(array $data, DeploymentObject $deployment = null): Server
    {
        // If a deployment object has been passed we need to get the allocation
        // that the server should use, and assign the cluster from that allocation.
        if ($deployment instanceof DeploymentObject) {
            $allocation = $this->configureDeployment($data, $deployment);
            $data['allocation_id'] = $allocation->id;
            $data['cluster_id'] = $allocation->cluster_id;
        }

        // Auto-configure the cluster based on the selected allocation
        // if no cluster was defined.
        if (empty($data['cluster_id'])) {
            Assert::false(empty($data['allocation_id']), 'Expected a non-empty allocation_id in server creation data.');

            $data['cluster_id'] = Allocation::query()->findOrFail($data['allocation_id'])->cluster_id;
        }

        if (empty($data['launchpad_id'])) {
            Assert::false(empty($data['rocket_id']), 'Expected a non-empty rocket_id in server creation data.');

            $data['launchpad_id'] = Rocket::query()->findOrFail($data['rocket_id'])->launchpad_id;
        }

        $rocketVariableData = $this->validatorService
            ->setUserLevel(User::USER_LEVEL_ADMIN)
            ->handle(Arr::get($data, 'rocket_id'), Arr::get($data, 'environment', []));

        // Due to the design of the Daemon, we need to persist this server to the disk
        // before we can actually create it on the Daemon.
        //
        // If that connection fails out we will attempt to perform a cleanup by just
        // deleting the server itself from the system.
        /** @var \Kubectyl\Models\Server $server */
        $server = $this->connection->transaction(function () use ($data, $rocketVariableData) {
            // Create the server and assign any additional allocations to it.
            $server = $this->createModel($data);

            // TODO: check this function
            isset($data['allocation_id']) ? $this->storeAssignedAllocations($server, $data) : null;
            $this->storeRocketVariables($server, $rocketVariableData);

            return $server;
        }, 5);

        try {
            $this->daemonServerRepository->setServer($server)->create(
                Arr::get($data, 'start_on_completion', false) ?? false
            );
        } catch (DaemonConnectionException $exception) {
            $this->serverDeletionService->withForce()->handle($server);

            throw $exception;
        }

        return $server;
    }

    /**
     * Gets an allocation to use for automatic deployment.
     *
     * @throws \Kubectyl\Exceptions\DisplayException
     * @throws \Kubectyl\Exceptions\Service\Deployment\NoViableAllocationException
     * @throws \Kubectyl\Exceptions\Service\Deployment\NoViableClusterException
     */
    private function configureDeployment(array $data, DeploymentObject $deployment): Allocation
    {
        /** @var \Illuminate\Support\Collection $clusters */
        $clusters = $this->findViableClustersService->setLocations($deployment->getLocations())
            ->handle();

        return $this->allocationSelectionService->setDedicated($deployment->isDedicated())
            ->setClusters($clusters->pluck('id')->toArray())
            ->setPorts($deployment->getPorts())
            ->handle();
    }

    /**
     * Store the server in the database and return the model.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    private function createModel(array $data): Server
    {
        $uuid = $this->generateUniqueUuidCombo();

        /** @var \Kubectyl\Models\Server $model */
        $model = $this->repository->create([
            'external_id' => Arr::get($data, 'external_id'),
            'uuid' => $uuid,
            'uuidShort' => substr($uuid, 0, 8),
            'cluster_id' => Arr::get($data, 'cluster_id'),
            'name' => Arr::get($data, 'name'),
            'description' => Arr::get($data, 'description') ?? '',
            'status' => Server::STATUS_INSTALLING,
            'skip_scripts' => Arr::get($data, 'skip_scripts') ?? isset($data['skip_scripts']),
            'owner_id' => Arr::get($data, 'owner_id'),
            'memory_request' => Arr::get($data, 'memory_request'),
            'memory_limit' => Arr::get($data, 'memory_limit'),
            'disk' => Arr::get($data, 'disk'),
            'storage_class' => Arr::get($data, 'storage_class'),
            'cpu_request' => Arr::get($data, 'cpu_request'),
            'cpu_limit' => Arr::get($data, 'cpu_limit'),
            'allocation_id' => Arr::get($data, 'allocation_id'),
            'default_port' => Arr::get($data, 'default_port'),
            'additional_ports' => Arr::get($data, 'additional_ports'),
            'launchpad_id' => Arr::get($data, 'launchpad_id'),
            'rocket_id' => Arr::get($data, 'rocket_id'),
            'startup' => Arr::get($data, 'startup'),
            'image' => Arr::get($data, 'image'),
            'database_limit' => Arr::get($data, 'database_limit') ?? 0,
            'allocation_limit' => Arr::get($data, 'allocation_limit') ?? 0,
            'snapshot_limit' => Arr::get($data, 'snapshot_limit') ?? 0,
            'node_selectors' => Arr::get($data, 'node_selectors'),
        ]);

        return $model;
    }

    /**
     * Configure the allocations assigned to this server.
     */
    private function storeAssignedAllocations(Server $server, array $data): void
    {
        $records = [$data['allocation_id']];
        if (isset($data['allocation_additional']) && is_array($data['allocation_additional'])) {
            $records = array_merge($records, $data['allocation_additional']);
        }

        Allocation::query()->whereIn('id', $records)->update([
            'server_id' => $server->id,
        ]);
    }

    /**
     * Process environment variables passed for this server and store them in the database.
     */
    private function storeRocketVariables(Server $server, Collection $variables): void
    {
        $records = $variables->map(function ($result) use ($server) {
            return [
                'server_id' => $server->id,
                'variable_id' => $result->id,
                'variable_value' => $result->value ?? '',
            ];
        })->toArray();

        if (!empty($records)) {
            $this->serverVariableRepository->insert($records);
        }
    }

    /**
     * Create a unique UUID and UUID-Short combo for a server.
     */
    private function generateUniqueUuidCombo(): string
    {
        $uuid = Uuid::uuid4()->toString();

        if (!$this->repository->isUniqueUuidCombo($uuid, substr($uuid, 0, 8))) {
            return $this->generateUniqueUuidCombo();
        }

        return $uuid;
    }
}
