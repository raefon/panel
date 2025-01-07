<?php

namespace Kubectyl\Http\Requests\Api\Application\Allocations;

use Kubectyl\Services\Acl\Api\AdminAcl;
use Kubectyl\Http\Requests\Api\Application\ApplicationApiRequest;

class GetAllocationsRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_ALLOCATIONS;

    protected int $permission = AdminAcl::READ;
}
