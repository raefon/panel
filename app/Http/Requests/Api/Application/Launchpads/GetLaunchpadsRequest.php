<?php

namespace Kubectyl\Http\Requests\Api\Application\Launchpads;

use Kubectyl\Services\Acl\Api\AdminAcl;
use Kubectyl\Http\Requests\Api\Application\ApplicationApiRequest;

class GetLaunchpadsRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_LAUNCHPADS;

    protected int $permission = AdminAcl::READ;
}
