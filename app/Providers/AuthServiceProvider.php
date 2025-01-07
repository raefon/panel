<?php

namespace Kubectyl\Providers;

use Kubectyl\Models\ApiKey;
use Kubectyl\Models\Server;
use Laravel\Sanctum\Sanctum;
use Kubectyl\Policies\ServerPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     */
    protected $policies = [
        Server::class => ServerPolicy::class,
    ];

    public function boot()
    {
        Sanctum::usePersonalAccessTokenModel(ApiKey::class);

        $this->registerPolicies();

        Gate::define('edit-post', function ($user, $post) {
            return $user->can('edit-post', $post);
        });
    }

    public function register()
    {
        Sanctum::ignoreMigrations();
    }
}
