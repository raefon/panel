<?php

namespace Kubectyl\Facades;

use Illuminate\Support\Facades\Facade;
use Kubectyl\Services\Activity\ActivityLogService;

class Activity extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ActivityLogService::class;
    }
}
