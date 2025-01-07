<?php

namespace Kubectyl\Http\Requests\Api\Client\Servers\Databases;

use Kubectyl\Models\Permission;
use Kubectyl\Contracts\Http\ClientPermissionsRequest;
use Kubectyl\Http\Requests\Api\Client\ClientApiRequest;

class GetDatabasesRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_DATABASE_READ;
    }
}
