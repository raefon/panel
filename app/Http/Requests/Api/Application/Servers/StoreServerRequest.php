<?php

namespace Kubectyl\Http\Requests\Api\Application\Servers;

use Kubectyl\Models\Server;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Kubectyl\Services\Acl\Api\AdminAcl;
use Kubectyl\Models\Objects\DeploymentObject;
use Kubectyl\Http\Requests\Api\Application\ApplicationApiRequest;

class StoreServerRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_SERVERS;

    protected int $permission = AdminAcl::WRITE;

    /**
     * Rules to be applied to this request.
     */
    public function rules(): array
    {
        $rules = Server::getRules();

        return [
            'external_id' => $rules['external_id'],
            'name' => $rules['name'],
            'cluster_id' => 'integer|min:1',
            'description' => array_merge(['nullable'], $rules['description']),
            'user' => $rules['owner_id'],
            'rocket' => $rules['rocket_id'],
            'docker_image' => $rules['image'],
            'startup' => $rules['startup'],
            'environment' => 'present|array',
            'skip_scripts' => 'sometimes|boolean',
            'node_selectors' => 'array|nullable',
            'node_selectors.*' => 'string',
            'storage_class' => 'string|nullable',

            // Resource limitations
            'limits' => 'required|array',
            'limits.memory_request' => $rules['memory_request'],
            'limits.memory_limit' => $rules['memory_limit'],
            'limits.disk' => $rules['disk'],
            'limits.cpu_request' => $rules['cpu_request'],
            'limits.cpu_limit' => $rules['cpu_limit'],

            // Application Resource Limits
            'feature_limits' => 'required|array',
            'feature_limits.databases' => $rules['database_limit'],
            'feature_limits.allocations' => $rules['allocation_limit'],
            'feature_limits.snapshots' => $rules['snapshot_limit'],

            // Placeholders for rules added in withValidator() function.
            'port.default' => '',
            'port.additional.*' => '',

            'allocation.default' => '',
            'allocation.additional.*' => '',

            // Automatic deployment rules
            'deploy' => 'sometimes|required|array',
            'deploy.locations' => 'array',
            'deploy.locations.*' => 'integer|min:1',
            'deploy.dedicated_ip' => 'required_with:deploy,boolean',
            'deploy.port_range' => 'array',
            'deploy.port_range.*' => 'string',

            'start_on_completion' => 'sometimes|boolean',
        ];
    }

    /**
     * Normalize the data into a format that can be consumed by the service.
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        return [
            'external_id' => array_get($data, 'external_id'),
            'name' => array_get($data, 'name'),
            'cluster_id' => array_get($data, 'cluster_id'),
            'description' => array_get($data, 'description'),
            'owner_id' => array_get($data, 'user'),
            'rocket_id' => array_get($data, 'rocket'),
            'node_selectors' => array_get($data, 'node_selectors'),
            'image' => array_get($data, 'docker_image'),
            'startup' => array_get($data, 'startup'),
            'environment' => array_get($data, 'environment'),
            'memory_request' => array_get($data, 'limits.memory_request'),
            'memory_limit' => array_get($data, 'limits.memory_limit'),
            'disk' => array_get($data, 'limits.disk'),
            'cpu_request' => array_get($data, 'limits.cpu_request'),
            'cpu_limit' => array_get($data, 'limits.cpu_limit'),
            'skip_scripts' => array_get($data, 'skip_scripts', false),
            'default_port' => array_get($data, 'port.default'),
            'additional_ports' => array_get($data, 'port.additional'),
            'allocation_id' => array_get($data, 'allocation.default'),
            'allocation_additional' => array_get($data, 'allocation.additional'),
            'start_on_completion' => array_get($data, 'start_on_completion', false),
            'database_limit' => array_get($data, 'feature_limits.databases'),
            'allocation_limit' => array_get($data, 'feature_limits.allocations'),
            'snapshot_limit' => array_get($data, 'feature_limits.snapshots'),
        ];
    }

    /*
     * Run validation after the rules above have been applied.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->sometimes('allocation.default', [
            'required', 'integer', 'bail',
            Rule::exists('allocations', 'id')->where(function ($query) {
                $query->whereNull('server_id');
            }),
        ], function ($input) use ($validator) {
            return !$input->deploy && (!isset($validator->getData()['port']['default']));
        });

        $validator->sometimes('allocation.additional.*', [
            'integer',
            Rule::exists('allocations', 'id')->where(function ($query) {
                $query->whereNull('server_id');
            }),
        ], function ($input) {
            return !$input->deploy;
        });

        $validator->sometimes('deploy.locations', 'present', function ($input) {
            return $input->deploy;
        });

        $validator->sometimes('deploy.port_range', 'present', function ($input) {
            return $input->deploy;
        });
    }

    /**
     * Return a deployment object that can be passed to the server creation service.
     */
    public function getDeploymentObject(): ?DeploymentObject
    {
        if (is_null($this->input('deploy'))) {
            return null;
        }

        $object = new DeploymentObject();
        $object->setDedicated($this->input('deploy.dedicated_ip', false));
        $object->setLocations($this->input('deploy.locations', []));
        $object->setPorts($this->input('deploy.port_range', []));

        return $object;
    }
}
