<?php

namespace Kubectyl\Http\Requests\Api\Application\Launchpads\Rockets;

use Kubectyl\Services\Acl\Api\AdminAcl;
use Kubectyl\Http\Requests\Api\Application\ApplicationApiRequest;

class GetRocketRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_ROCKETS;

    protected int $permission = AdminAcl::READ;
}
