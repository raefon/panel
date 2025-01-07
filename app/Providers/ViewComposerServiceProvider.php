<?php

namespace Kubectyl\Providers;

use Illuminate\Support\ServiceProvider;
use Kubectyl\Http\ViewComposers\AssetComposer;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function boot()
    {
        $this->app->make('view')->composer('*', AssetComposer::class);
    }
}
