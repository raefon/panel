<?php

namespace Kubectyl\Http\Requests\Api\Client\Servers\Schedules;

use Kubectyl\Models\Permission;

class DeleteScheduleRequest extends ViewScheduleRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SCHEDULE_DELETE;
    }
}
