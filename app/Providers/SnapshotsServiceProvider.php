<?php

namespace Kubectyl\Providers;

use Illuminate\Support\ServiceProvider;
use Kubectyl\Extensions\Snapshots\SnapshotManager;
use Illuminate\Contracts\Support\DeferrableProvider;

class SnapshotsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the S3 snapshot disk.
     */
    public function register()
    {
        $this->app->singleton(SnapshotManager::class, function ($app) {
            return new SnapshotManager($app);
        });
    }

    public function provides(): array
    {
        return [SnapshotManager::class];
    }
}
