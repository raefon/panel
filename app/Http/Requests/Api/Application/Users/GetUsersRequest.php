<?php

namespace Kubectyl\Http\Requests\Api\Application\Users;

use Kubectyl\Services\Acl\Api\AdminAcl as Acl;
use Kubectyl\Http\Requests\Api\Application\ApplicationApiRequest;

class GetUsersRequest extends ApplicationApiRequest
{
    protected ?string $resource = Acl::RESOURCE_USERS;

    protected int $permission = Acl::READ;
}
