<?php

namespace Kubectyl\Facades;

use Illuminate\Support\Facades\Facade;
use Kubectyl\Services\Activity\ActivityLogBatchService;

class LogBatch extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ActivityLogBatchService::class;
    }
}
