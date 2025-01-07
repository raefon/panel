<?php

namespace Kubectyl\Http\Requests\Api\Client\Servers\Startup;

use Kubectyl\Models\Permission;
use Kubectyl\Http\Requests\Api\Client\ClientApiRequest;

class GetStartupRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_STARTUP_READ;
    }
}
