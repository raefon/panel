<?php

namespace Kubectyl\Providers;

use Illuminate\Support\ServiceProvider;
use Kubectyl\Repositories\Eloquent\TaskRepository;
use Kubectyl\Repositories\Eloquent\UserRepository;
use Kubectyl\Repositories\Eloquent\ApiKeyRepository;
use Kubectyl\Repositories\Eloquent\RocketRepository;
use Kubectyl\Repositories\Eloquent\ServerRepository;
use Kubectyl\Repositories\Eloquent\ClusterRepository;
use Kubectyl\Repositories\Eloquent\SessionRepository;
use Kubectyl\Repositories\Eloquent\SubuserRepository;
use Kubectyl\Repositories\Eloquent\DatabaseRepository;
use Kubectyl\Repositories\Eloquent\LocationRepository;
use Kubectyl\Repositories\Eloquent\ScheduleRepository;
use Kubectyl\Repositories\Eloquent\SettingsRepository;
use Kubectyl\Repositories\Eloquent\LaunchpadRepository;
use Kubectyl\Repositories\Eloquent\AllocationRepository;
use Kubectyl\Contracts\Repository\TaskRepositoryInterface;
use Kubectyl\Contracts\Repository\UserRepositoryInterface;
use Kubectyl\Repositories\Eloquent\DatabaseHostRepository;
use Kubectyl\Contracts\Repository\ApiKeyRepositoryInterface;
use Kubectyl\Contracts\Repository\RocketRepositoryInterface;
use Kubectyl\Contracts\Repository\ServerRepositoryInterface;
use Kubectyl\Repositories\Eloquent\RocketVariableRepository;
use Kubectyl\Repositories\Eloquent\ServerVariableRepository;
use Kubectyl\Contracts\Repository\ClusterRepositoryInterface;
use Kubectyl\Contracts\Repository\SessionRepositoryInterface;
use Kubectyl\Contracts\Repository\SubuserRepositoryInterface;
use Kubectyl\Contracts\Repository\DatabaseRepositoryInterface;
use Kubectyl\Contracts\Repository\LocationRepositoryInterface;
use Kubectyl\Contracts\Repository\ScheduleRepositoryInterface;
use Kubectyl\Contracts\Repository\SettingsRepositoryInterface;
use Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface;
use Kubectyl\Contracts\Repository\AllocationRepositoryInterface;
use Kubectyl\Contracts\Repository\DatabaseHostRepositoryInterface;
use Kubectyl\Contracts\Repository\RocketVariableRepositoryInterface;
use Kubectyl\Contracts\Repository\ServerVariableRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register all of the repository bindings.
     */
    public function register()
    {
        // Eloquent Repositories
        $this->app->bind(AllocationRepositoryInterface::class, AllocationRepository::class);
        $this->app->bind(ApiKeyRepositoryInterface::class, ApiKeyRepository::class);
        $this->app->bind(DatabaseRepositoryInterface::class, DatabaseRepository::class);
        $this->app->bind(DatabaseHostRepositoryInterface::class, DatabaseHostRepository::class);
        $this->app->bind(RocketRepositoryInterface::class, RocketRepository::class);
        $this->app->bind(RocketVariableRepositoryInterface::class, RocketVariableRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(LaunchpadRepositoryInterface::class, LaunchpadRepository::class);
        $this->app->bind(ClusterRepositoryInterface::class, ClusterRepository::class);
        $this->app->bind(ScheduleRepositoryInterface::class, ScheduleRepository::class);
        $this->app->bind(ServerRepositoryInterface::class, ServerRepository::class);
        $this->app->bind(ServerVariableRepositoryInterface::class, ServerVariableRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(SubuserRepositoryInterface::class, SubuserRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }
}
