<?php

namespace Kubectyl\Http\Controllers\Admin\Servers;

use Carbon\CarbonImmutable;
use Kubectyl\Models\Server;
use Illuminate\Http\Request;
use Kubectyl\Models\ServerTransfer;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\Database\ConnectionInterface;
use Kubectyl\Services\Clusters\ClusterJWTService;
use Kubectyl\Repositories\Eloquent\ClusterRepository;
use Kubectyl\Repositories\Kuber\DaemonTransferRepository;
use Kubectyl\Contracts\Repository\AllocationRepositoryInterface;

class ServerTransferController extends Controller
{
    /**
     * ServerTransferController constructor.
     */
    public function __construct(
        private AlertsMessageBag $alert,
        private AllocationRepositoryInterface $allocationRepository,
        private ConnectionInterface $connection,
        private DaemonTransferRepository $daemonTransferRepository,
        private ClusterJWTService $clusterJWTService,
        private ClusterRepository $clusterRepository
    ) {
    }

    /**
     * Starts a transfer of a server to a new cluster.
     *
     * @throws \Throwable
     */
    public function transfer(Request $request, Server $server): RedirectResponse
    {
        $validatedData = $request->validate([
            'cluster_id' => 'required|exists:clusters,id',
            'allocation_id' => 'required|bail|unique:servers|exists:allocations,id',
            'allocation_additional' => 'nullable',
        ]);

        $cluster_id = $validatedData['cluster_id'];
        $allocation_id = intval($validatedData['allocation_id']);
        $additional_allocations = array_map('intval', $validatedData['allocation_additional'] ?? []);

        $server->validateTransferState();

        $this->connection->transaction(function () use ($server, $cluster_id, $allocation_id, $additional_allocations) {
            // Create a new ServerTransfer entry.
            $transfer = new ServerTransfer();

            $transfer->server_id = $server->id;
            $transfer->old_cluster = $server->cluster_id;
            $transfer->new_cluster = $cluster_id;
            $transfer->old_allocation = $server->allocation_id;
            $transfer->new_allocation = $allocation_id;
            $transfer->old_additional_allocations = $server->allocations->where('id', '!=', $server->allocation_id)->pluck('id');
            $transfer->new_additional_allocations = $additional_allocations;

            $transfer->save();

            // Add the allocations to the server, so they cannot be automatically assigned while the transfer is in progress.
            $this->assignAllocationsToServer($server, $cluster_id, $allocation_id, $additional_allocations);

            // Generate a token for the destination cluster that the source cluster can use to authenticate with.
            $token = $this->clusterJWTService
                ->setExpiresAt(CarbonImmutable::now()->addMinutes(15))
                ->setSubject($server->uuid)
                ->handle($transfer->newCluster, $server->uuid, 'sha256');

            // Notify the source cluster of the pending outgoing transfer.
            $this->daemonTransferRepository->setServer($server)->notify($transfer->newCluster, $token);

            return $transfer;
        });

        $this->alert->success(trans('admin/server.alerts.transfer_started'))->flash();

        return redirect()->route('admin.servers.view.manage', $server->id);
    }

    /**
     * Assigns the specified allocations to the specified server.
     */
    private function assignAllocationsToServer(Server $server, int $cluster_id, int $allocation_id, array $additional_allocations)
    {
        $allocations = $additional_allocations;
        $allocations[] = $allocation_id;

        $unassigned = $this->allocationRepository->getUnassignedAllocationIds($cluster_id);

        $updateIds = [];
        foreach ($allocations as $allocation) {
            if (!in_array($allocation, $unassigned)) {
                continue;
            }

            $updateIds[] = $allocation;
        }

        if (!empty($updateIds)) {
            $this->allocationRepository->updateWhereIn('id', $updateIds, ['server_id' => $server->id]);
        }
    }
}
