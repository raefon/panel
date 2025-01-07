<?php

namespace Kubectyl\Http\Requests\Api\Application\Servers\Databases;

use Kubectyl\Services\Acl\Api\AdminAcl;

class ServerDatabaseWriteRequest extends GetServerDatabasesRequest
{
    protected int $permission = AdminAcl::WRITE;
}
