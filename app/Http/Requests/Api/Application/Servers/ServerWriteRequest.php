<?php

namespace Kubectyl\Http\Requests\Api\Application\Servers;

use Kubectyl\Services\Acl\Api\AdminAcl;
use Kubectyl\Http\Requests\Api\Application\ApplicationApiRequest;

class ServerWriteRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_SERVERS;

    protected int $permission = AdminAcl::WRITE;
}
