<?php

namespace Kubectyl\Http\Requests\Api\Application\Clusters;

use Kubectyl\Models\Cluster;
use Kubectyl\Services\Acl\Api\AdminAcl;
use Kubectyl\Http\Requests\Api\Application\ApplicationApiRequest;

class StoreClusterRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_CLUSTERS;

    protected int $permission = AdminAcl::WRITE;

    /**
     * Validation rules to apply to this request.
     */
    public function rules(array $rules = null): array
    {
        return collect($rules ?? Cluster::getRules())->only([
            'public',
            'name',
            'location_id',
            'fqdn',
            'scheme',
            'behind_proxy',
            'maintenance_mode',
            'upload_size',
            'daemonListen',
            'daemonBase',
            'host',
            'bearer_token',
            'insecure',
            'service_type',
            'storage_class',
            'ns',
        ])->mapWithKeys(function ($value, $key) {
            $key = ($key === 'daemonSFTP') ? 'daemonSftp' : $key;

            return [snake_case($key) => $value];
        })->toArray();
    }

    /**
     * Fields to rename for clarity in the API response.
     */
    public function attributes(): array
    {
        return [
            'daemon_base' => 'Daemon Base Path',
            'upload_size' => 'File Upload Size Limit',
            'location_id' => 'Location',
            'public' => 'Cluster Visibility',
        ];
    }

    /**
     * Change the formatting of some data keys in the validated response data
     * to match what the application expects in the services.
     */
    public function validated($key = null, $default = null): array
    {
        $response = parent::validated();
        $response['daemonListen'] = $response['daemon_listen'];
        $response['daemonSFTP'] = $response['daemon_sftp'];
        $response['daemonBase'] = $response['daemon_base'] ?? (new Cluster())->getAttribute('daemonBase');

        unset($response['daemon_base'], $response['daemon_listen'], $response['daemon_sftp']);

        return $response;
    }
}
