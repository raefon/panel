<?php

namespace Kubectyl\Services\Telemetry;

use Exception;
use Ramsey\Uuid\Uuid;
use Kubectyl\Models\User;
use Kubectyl\Models\Mount;
use Illuminate\Support\Arr;
use Kubectyl\Models\Rocket;
use Kubectyl\Models\Server;
use Kubectyl\Models\Cluster;
use Kubectyl\Models\Location;
use Kubectyl\Models\Snapshot;
use Kubectyl\Models\Launchpad;
use Kubectyl\Models\Allocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Kubectyl\Repositories\Eloquent\SettingsRepository;
use Kubectyl\Repositories\Kuber\DaemonConfigurationRepository;

class TelemetryCollectionService
{
    /**
     * TelemetryCollectionService constructor.
     */
    public function __construct(
        private DaemonConfigurationRepository $daemonConfigurationRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    /**
     * Collects telemetry data and sends it to the Kubectyl Telemetry Service.
     */
    public function __invoke(): void
    {
        try {
            $data = $this->collect();
        } catch (Exception) {
            return;
        }

        Http::post('https://telemetry.kubectyl.org', $data);
    }

    /**
     * Collects telemetry data and returns it as an array.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    public function collect(): array
    {
        $uuid = $this->settingsRepository->get('app:telemetry:uuid');
        if (is_null($uuid)) {
            $uuid = Uuid::uuid4()->toString();
            $this->settingsRepository->set('app:telemetry:uuid', $uuid);
        }

        $clusters = Cluster::all()->map(function ($cluster) {
            try {
                $info = $this->daemonConfigurationRepository->setCluster($cluster)->getSystemInformation(2);
            } catch (Exception) {
                return null;
            }

            return [
                'id' => $cluster->uuid,
                'version' => Arr::get($info, 'version', ''),

                'kubernetes' => [
                    'version' => Arr::get($info, 'kubernetes.version', ''),
                    'nodes' => Arr::get($info, 'kubernetes.nodes', ''),
                    'pod_status' => Arr::get($info, 'kubernetes.pod_status', ''),
                ],

                'system' => [
                    'architecture' => Arr::get($info, 'system.architecture', ''),
                    'cpuThreads' => Arr::get($info, 'system.cpu_threads', ''),
                    'memoryBytes' => Arr::get($info, 'system.memory_bytes', ''),
                    'kernelVersion' => Arr::get($info, 'system.kernel_version', ''),
                    'os' => Arr::get($info, 'system.os', ''),
                    'osType' => Arr::get($info, 'system.os_type', ''),
                ],
            ];
        })->filter(fn ($cluster) => !is_null($cluster))->toArray();

        return [
            'id' => $uuid,

            'panel' => [
                'version' => config('app.version'),
                'phpVersion' => phpversion(),

                'drivers' => [
                    'cache' => [
                        'type' => config('cache.default'),
                    ],

                    'database' => [
                        'type' => config('database.default'),
                        'version' => DB::getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
                    ],
                ],
            ],

            'resources' => [
                'allocations' => [
                    'count' => Allocation::count(),
                    'used' => Allocation::whereNotNull('server_id')->count(),
                ],

                'snapshots' => [
                    'count' => Snapshot::count(),
                    'bytes' => Snapshot::sum('bytes'),
                ],

                'rockets' => [
                    'count' => Rocket::count(),
                    // Rocket UUIDs are generated randomly on import, so there is not a consistent way to
                    // determine if servers are using default rockets or not.
                   'server_usage' => Rocket::all()
                       ->flatMap(fn (Rocket $rocket) => [$rocket->uuid => $rocket->servers->count()])
                       ->filter(fn (int $count) => $count > 0)
                       ->toArray(),
                ],

                'locations' => [
                    'count' => Location::count(),
                ],

                'mounts' => [
                    'count' => Mount::count(),
                ],

                'launchpads' => [
                    'count' => Launchpad::count(),
                    // Launchpad UUIDs are generated randomly on import, so there is not a consistent way to
                    // determine if servers are using default rockets or not.
                   'server_usage' => Launchpad::all()
                       ->flatMap(fn (Launchpad $launchpad) => [$launchpad->uuid => $launchpad->rockets->sum(fn (Rocket $rocket) => $rocket->servers->count())])
                       ->filter(fn (int $count) => $count > 0)
                       ->toArray(),
                ],

                'clusters' => [
                    'count' => Cluster::count(),
                ],

                'servers' => [
                    'count' => Server::count(),
                    'suspended' => Server::where('status', Server::STATUS_SUSPENDED)->count(),
                ],

                'users' => [
                    'count' => User::count(),
                    'admins' => User::where('root_admin', true)->count(),
                ],
            ],

            'clusters' => $clusters,
        ];
    }
}
