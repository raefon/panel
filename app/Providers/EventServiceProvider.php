<?php

namespace Kubectyl\Providers;

use Kubectyl\Models\User;
use Kubectyl\Models\Server;
use Kubectyl\Models\Subuser;
use Kubectyl\Models\RocketVariable;
use Kubectyl\Observers\UserObserver;
use Kubectyl\Observers\ServerObserver;
use Kubectyl\Observers\SubuserObserver;
use Kubectyl\Observers\RocketVariableObserver;
use Kubectyl\Listeners\Auth\AuthenticationListener;
use Kubectyl\Events\Server\Installed as ServerInstalledEvent;
use Kubectyl\Notifications\ServerInstalled as ServerInstalledNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        ServerInstalledEvent::class => [ServerInstalledNotification::class],
    ];

    protected $subscribe = [
        AuthenticationListener::class,
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        User::observe(UserObserver::class);
        Server::observe(ServerObserver::class);
        Subuser::observe(SubuserObserver::class);
        RocketVariable::observe(RocketVariableObserver::class);
    }
}
