<?php

namespace Kubectyl\Http\Requests\Api\Application\Servers;

use Kubectyl\Models\Server;
use Illuminate\Support\Collection;

class UpdateServerBuildConfigurationRequest extends ServerWriteRequest
{
    /**
     * Return the rules to validate this request against.
     */
    public function rules(): array
    {
        $rules = Server::getRulesForUpdate($this->parameter('server', Server::class));

        return [
            'allocation' => $rules['allocation_id'],

            'limits' => 'sometimes|array',
            'limits.memory_request' => $this->requiredToOptional('memory_request', $rules['memory_request'], true),
            'limits.memory_limit' => $this->requiredToOptional('memory_limit', $rules['memory_limit'], true),
            'limits.cpu_request' => $this->requiredToOptional('cpu_request', $rules['cpu_request'], true),
            'limits.cpu_limit' => $this->requiredToOptional('cpu_limit', $rules['cpu_limit'], true),
            'limits.disk' => $this->requiredToOptional('disk', $rules['disk'], true),

            // Legacy rules to maintain backwards compatable API support without requiring
            // a major version bump.
            //
            // @see https://github.com/pterodactyl/panel/issues/1500
            'memory_request' => $this->requiredToOptional('memory_request', $rules['memory_request']),
            'memory_limit' => $this->requiredToOptional('memory_limit', $rules['memory_limit']),
            'cpu_request' => $this->requiredToOptional('cpu_request', $rules['cpu_request']),
            'cpu_limit' => $this->requiredToOptional('cpu_limit', $rules['cpu_limit']),
            'disk' => $this->requiredToOptional('disk', $rules['disk']),

            'add_ports' => 'bail|array',
            'add_ports.*' => 'integer',
            'remove_ports' => 'bail|array',
            'remove_ports.*' => 'integer',

            'add_allocations' => 'bail|array',
            'add_allocations.*' => 'integer',
            'remove_allocations' => 'bail|array',
            'remove_allocations.*' => 'integer',

            'feature_limits' => 'required|array',
            'feature_limits.databases' => $rules['database_limit'],
            'feature_limits.allocations' => $rules['allocation_limit'],
            'feature_limits.snapshots' => $rules['snapshot_limit'],

            'storage_class' => $rules['storage_class'],
        ];
    }

    /**
     * Convert the allocation field into the expected format for the service handler.
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        $data['allocation_id'] = $data['allocation'];
        $data['database_limit'] = $data['feature_limits']['databases'] ?? null;
        $data['allocation_limit'] = $data['feature_limits']['allocations'] ?? null;
        $data['snapshot_limit'] = $data['feature_limits']['snapshots'] ?? null;
        unset($data['allocation'], $data['feature_limits']);

        // Adjust the limits field to match what is expected by the model.
        if (!empty($data['limits'])) {
            foreach ($data['limits'] as $key => $value) {
                $data[$key] = $value;
            }

            unset($data['limits']);
        }

        return $data;
    }

    /**
     * Custom attributes to use in error message responses.
     */
    public function attributes(): array
    {
        return [
            'add_allocations' => 'allocations to add',
            'remove_allocations' => 'allocations to remove',
            'add_allocations.*' => 'allocation to add',
            'remove_allocations.*' => 'allocation to remove',
            'feature_limits.databases' => 'Database Limit',
            'feature_limits.allocations' => 'Allocation Limit',
            'feature_limits.snapshots' => 'Snapshot Limit',
        ];
    }

    /**
     * Converts existing rules for certain limits into a format that maintains backwards
     * compatability with the old API endpoint while also supporting a more correct API
     * call.
     *
     * @see https://github.com/pterodactyl/panel/issues/1500
     */
    protected function requiredToOptional(string $field, array $rules, bool $limits = false): array
    {
        if (!in_array('required', $rules)) {
            return $rules;
        }

        return (new Collection($rules))
            ->filter(function ($value) {
                return $value !== 'required';
            })
            ->prepend($limits ? 'required_with:limits' : 'required_without:limits')
            ->toArray();
    }
}
