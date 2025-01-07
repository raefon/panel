<?php

namespace Kubectyl\Facades;

use Illuminate\Support\Facades\Facade;
use Kubectyl\Services\Activity\ActivityLogTargetableService;

class LogTarget extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ActivityLogTargetableService::class;
    }
}
