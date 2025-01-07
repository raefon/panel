<?php

namespace Kubectyl\Http\Requests\Api\Client\Servers\Schedules;

use Kubectyl\Models\Permission;

class UpdateScheduleRequest extends StoreScheduleRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SCHEDULE_UPDATE;
    }
}
