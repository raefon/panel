<?php

namespace Kubectyl\Http\Requests\Api\Application\Servers;

use Kubectyl\Models\Server;
use Kubectyl\Services\Acl\Api\AdminAcl;
use Kubectyl\Http\Requests\Api\Application\ApplicationApiRequest;

class UpdateServerStartupRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_SERVERS;

    protected int $permission = AdminAcl::WRITE;

    /**
     * Validation rules to run the input against.
     */
    public function rules(): array
    {
        $data = Server::getRulesForUpdate($this->parameter('server', Server::class));

        return [
            'startup' => $data['startup'],
            'environment' => 'present|array',
            'rocket' => $data['rocket_id'],
            'image' => $data['image'],
            'skip_scripts' => 'present|boolean',
        ];
    }

    /**
     * Return the validated data in a format that is expected by the service.
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        return collect($data)->only(['startup', 'environment', 'skip_scripts'])->merge([
            'rocket_id' => array_get($data, 'rocket'),
            'docker_image' => array_get($data, 'image'),
        ])->toArray();
    }
}
