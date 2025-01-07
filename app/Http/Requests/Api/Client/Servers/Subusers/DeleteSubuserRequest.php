<?php

namespace Kubectyl\Http\Requests\Api\Client\Servers\Subusers;

use Kubectyl\Models\Permission;

class DeleteSubuserRequest extends SubuserRequest
{
    public function permission(): string
    {
        return Permission::ACTION_USER_DELETE;
    }
}
