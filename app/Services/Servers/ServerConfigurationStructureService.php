<?php

namespace Kubectyl\Services\Servers;

use Kubectyl\Models\Mount;
use Kubectyl\Models\Server;

class ServerConfigurationStructureService
{
    /**
     * ServerConfigurationStructureService constructor.
     */
    public function __construct(private EnvironmentService $environment)
    {
    }

    /**
     * Return a configuration array for a specific server when passed a server model.
     *
     * DO NOT MODIFY THIS FUNCTION. This powers legacy code handling for the new Wings
     * daemon, if you modify the structure rockets will break unexpectedly.
     */
    public function handle(Server $server, array $override = [], bool $legacy = false): array
    {
        $clone = $server;
        // If any overrides have been set on this call make sure to update them on the
        // cloned instance so that the configuration generated uses them.
        if (!empty($override)) {
            $clone = $server->fresh();
            foreach ($override as $key => $value) {
                $clone->setAttribute($key, $value);
            }
        }

        return $legacy
            ? $this->returnLegacyFormat($clone)
            : $this->returnCurrentFormat($clone);
    }

    /**
     * Returns the new data format used for the Kuber daemon.
     */
    protected function returnCurrentFormat(Server $server): array
    {
        $array = [
            'uuid' => $server->uuid,
            'meta' => [
                'name' => $server->name,
                'description' => $server->description,
            ],
            'suspended' => $server->isSuspended(),
            'environment' => $this->environment->handle($server),
            'invocation' => $server->startup,
            'skip_rocket_scripts' => $server->skip_scripts,
            'build' => [
                'memory_request' => $server->memory_request,
                'memory_limit' => $server->memory_limit,
                'cpu_request' => $server->cpu_request,
                'cpu_limit' => $server->cpu_limit,
                'disk_space' => $server->disk,
            ],
            'storage_class' => $server->storage_class,
            'container' => [
                'image' => $server->image,
                'requires_rebuild' => false,
            ],
            'mounts' => $server->mounts->map(function (Mount $mount) {
                return [
                    'source' => $mount->source,
                    'target' => $mount->target,
                    'read_only' => $mount->read_only,
                ];
            }),
            'rocket' => [
                'id' => $server->rocket->uuid,
                'file_denylist' => $server->rocket->inherit_file_denylist,
            ],
            'node_selectors' => $server->node_selectors,
        ];

        if (!empty($server->default_port)) {
            $array['ports'] = [
                'default' => [
                    'port' => $server->default_port,
                ],
                'mappings' => $server->additional_ports ? $server->additional_ports : [],
            ];
        } else {
            $array['allocations'] = [
                'default' => [
                    'ip' => $ip = $server->allocation ? $server->allocation->ip : null,
                    'port' => $server->allocation ? $server->allocation->port : null,
                ],
                'mappings' => $server->getAllocationMappings(),
            ];
        }

        return $array;
    }

    /**
     * Returns the legacy server data format to continue support for old rocket configurations
     * that have not yet been updated.
     *
     * @deprecated
     */
    protected function returnLegacyFormat(Server $server): array
    {
        return [
            'uuid' => $server->uuid,
            'build' => [
                'default' => [
                    'ip' => !empty($server->allocation) ? $server->allocation->ip : '0.0.0.0',
                    'port' => $server->default_port ? $server->default_port : $server->allocation->port,
                ],
                'env' => $this->environment->handle($server),
                'memory_request' => (int) $server->memory_request,
                'memory_limit' => (int) $server->memory_limit,
                'cpu_request' => (int) $server->cpu_request,
                'cpu_limit' => (int) $server->cpu_limit,
                'disk' => (int) $server->disk,
                'image' => $server->image,
            ],
            'service' => [
                'rocket' => $server->rocket->uuid,
                'skip_scripts' => $server->skip_scripts,
            ],
            'rebuild' => false,
            'suspended' => $server->isSuspended() ? 1 : 0,
        ];
    }
}
