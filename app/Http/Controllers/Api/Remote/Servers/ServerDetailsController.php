<?php

namespace Kubectyl\Http\Controllers\Api\Remote\Servers;

use Kubectyl\Models\Server;
use Illuminate\Http\Request;
use Kubectyl\Facades\Activity;
use Illuminate\Http\JsonResponse;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\Database\ConnectionInterface;
use Kubectyl\Repositories\Eloquent\ServerRepository;
use Kubectyl\Services\Rockets\RocketConfigurationService;
use Kubectyl\Http\Resources\Kuber\ServerConfigurationCollection;
use Kubectyl\Services\Servers\ServerConfigurationStructureService;

class ServerDetailsController extends Controller
{
    /**
     * ServerConfigurationController constructor.
     */
    public function __construct(
        protected ConnectionInterface $connection,
        private ServerRepository $repository,
        private ServerConfigurationStructureService $configurationStructureService,
        private RocketConfigurationService $rocketConfigurationService
    ) {
    }

    /**
     * Returns details about the server that allows Wings to self-recover and ensure
     * that the state of the server matches the Panel at all times.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        $server = $this->repository->getByUuid($uuid);

        return new JsonResponse([
            'settings' => $this->configurationStructureService->handle($server),
            'process_configuration' => $this->rocketConfigurationService->handle($server),
        ]);
    }

    /**
     * Lists all servers with their configurations that are assigned to the requesting cluster.
     */
    public function list(Request $request): ServerConfigurationCollection
    {
        /** @var \Kubectyl\Models\Cluster $cluster */
        $cluster = $request->attributes->get('cluster');

        // Avoid run-away N+1 SQL queries by preloading the relationships that are used
        // within each of the services called below.
        $servers = Server::query()->with('allocations', 'rocket', 'mounts', 'variables', 'location')
            ->where('cluster_id', $cluster->id)
            // If you don't cast this to a string you'll end up with a stringified per_page returned in
            // the metadata, and then Wings will panic crash as a result.
            ->paginate((int) $request->input('per_page', 50));

        return new ServerConfigurationCollection($servers);
    }

    /**
     * Resets the state of all servers on the cluster to be normal. This is triggered
     * when Wings restarts and is useful for ensuring that any servers on the cluster
     * do not get incorrectly stuck in installing/restoring from snapshot states since
     * a Wings reboot would completely stop those processes.
     *
     * @throws \Throwable
     */
    public function resetState(Request $request): JsonResponse
    {
        $cluster = $request->attributes->get('cluster');

        // Get all the servers that are currently marked as restoring from a snapshot
        // on this cluster that do not have a failed snapshot tracked in the audit logs table
        // as well.
        //
        // For each of those servers we'll track a new audit log entry to mark them as
        // failed and then update them all to be in a valid state.
        $servers = Server::query()
            ->with([
                'activity' => fn ($builder) => $builder
                    ->where('activity_logs.event', 'server:snapshot.restore-started')
                    ->latest('timestamp'),
            ])
            ->where('cluster_id', $cluster->id)
            ->where('status', Server::STATUS_RESTORING_SNAPSHOT)
            ->get();

        $this->connection->transaction(function () use ($cluster, $servers) {
            /** @var \Kubectyl\Models\Server $server */
            foreach ($servers as $server) {
                /** @var \Kubectyl\Models\ActivityLog|null $activity */
                $activity = $server->activity->first();
                if (!is_null($activity)) {
                    if ($subject = $activity->subjects->where('subject_type', 'snapshot')->first()) {
                        // Just create a new audit entry for this event and update the server state
                        // so that power actions, file management, and snapshots can resume as normal.
                        Activity::event('server:snapshot.restore-failed')
                            ->subject($server, $subject->subject)
                            ->property('name', $subject->subject->name)
                            ->log();
                    }
                }
            }

            // Update any server marked as installing or restoring as being in a normal state
            // at this point in the process.
            Server::query()->where('cluster_id', $cluster->id)
                ->whereIn('status', [Server::STATUS_INSTALLING, Server::STATUS_RESTORING_SNAPSHOT])
                ->update(['status' => null]);
        });

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
