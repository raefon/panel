<?php

use Illuminate\Support\Facades\Route;
use Kubectyl\Http\Controllers\Api\Application;

/*
|--------------------------------------------------------------------------
| User Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /api/application/users
|
*/

Route::group(['prefix' => '/users'], function () {
    Route::get('/', [Application\Users\UserController::class, 'index'])->name('api.application.users');
    Route::get('/{user:id}', [Application\Users\UserController::class, 'view'])->name('api.application.users.view');
    Route::get('/external/{external_id}', [Application\Users\ExternalUserController::class, 'index'])->name('api.application.users.external');

    Route::post('/', [Application\Users\UserController::class, 'store']);
    Route::patch('/{user:id}', [Application\Users\UserController::class, 'update']);

    Route::delete('/{user:id}', [Application\Users\UserController::class, 'delete']);
});

/*
|--------------------------------------------------------------------------
| Cluster Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /api/application/clusters
|
*/
Route::group(['prefix' => '/clusters'], function () {
    Route::get('/', [Application\Clusters\ClusterController::class, 'index'])->name('api.application.clusters');
    Route::get('/deployable', Application\Clusters\ClusterDeploymentController::class);
    Route::get('/{cluster:id}', [Application\Clusters\ClusterController::class, 'view'])->name('api.application.clusters.view');
    Route::get('/{cluster:id}/configuration', Application\Clusters\ClusterConfigurationController::class);

    Route::post('/', [Application\Clusters\ClusterController::class, 'store']);
    Route::patch('/{cluster:id}', [Application\Clusters\ClusterController::class, 'update']);

    Route::delete('/{cluster:id}', [Application\Clusters\ClusterController::class, 'delete']);

    Route::group(['prefix' => '/{cluster:id}/allocations'], function () {
        Route::get('/', [Application\Clusters\AllocationController::class, 'index'])->name('api.application.allocations');
        Route::post('/', [Application\Clusters\AllocationController::class, 'store']);
        Route::delete('/{allocation:id}', [Application\Clusters\AllocationController::class, 'delete'])->name('api.application.allocations.view');
    });
});

/*
|--------------------------------------------------------------------------
| Location Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /api/application/locations
|
*/
Route::group(['prefix' => '/locations'], function () {
    Route::get('/', [Application\Locations\LocationController::class, 'index'])->name('api.applications.locations');
    Route::get('/{location:id}', [Application\Locations\LocationController::class, 'view'])->name('api.application.locations.view');

    Route::post('/', [Application\Locations\LocationController::class, 'store']);
    Route::patch('/{location:id}', [Application\Locations\LocationController::class, 'update']);

    Route::delete('/{location:id}', [Application\Locations\LocationController::class, 'delete']);
});

/*
|--------------------------------------------------------------------------
| Server Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /api/application/servers
|
*/
Route::group(['prefix' => '/servers'], function () {
    Route::get('/', [Application\Servers\ServerController::class, 'index'])->name('api.application.servers');
    Route::get('/{server:id}', [Application\Servers\ServerController::class, 'view'])->name('api.application.servers.view');
    Route::get('/external/{external_id}', [Application\Servers\ExternalServerController::class, 'index'])->name('api.application.servers.external');

    Route::patch('/{server:id}/details', [Application\Servers\ServerDetailsController::class, 'details'])->name('api.application.servers.details');
    Route::patch('/{server:id}/build', [Application\Servers\ServerDetailsController::class, 'build'])->name('api.application.servers.build');
    Route::patch('/{server:id}/startup', [Application\Servers\StartupController::class, 'index'])->name('api.application.servers.startup');

    Route::post('/', [Application\Servers\ServerController::class, 'store']);
    Route::post('/{server:id}/suspend', [Application\Servers\ServerManagementController::class, 'suspend'])->name('api.application.servers.suspend');
    Route::post('/{server:id}/unsuspend', [Application\Servers\ServerManagementController::class, 'unsuspend'])->name('api.application.servers.unsuspend');
    Route::post('/{server:id}/reinstall', [Application\Servers\ServerManagementController::class, 'reinstall'])->name('api.application.servers.reinstall');

    Route::delete('/{server:id}', [Application\Servers\ServerController::class, 'delete']);
    Route::delete('/{server:id}/{force?}', [Application\Servers\ServerController::class, 'delete']);

    // Database Management Endpoint
    Route::group(['prefix' => '/{server:id}/databases'], function () {
        Route::get('/', [Application\Servers\DatabaseController::class, 'index'])->name('api.application.servers.databases');
        Route::get('/{database:id}', [Application\Servers\DatabaseController::class, 'view'])->name('api.application.servers.databases.view');

        Route::post('/', [Application\Servers\DatabaseController::class, 'store']);
        Route::post('/{database:id}/reset-password', [Application\Servers\DatabaseController::class, 'resetPassword']);

        Route::delete('/{database:id}', [Application\Servers\DatabaseController::class, 'delete']);
    });
});

/*
|--------------------------------------------------------------------------
| Launchpad Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /api/application/launchpads
|
*/
Route::group(['prefix' => '/launchpads'], function () {
    Route::get('/', [Application\Launchpads\LaunchpadController::class, 'index'])->name('api.application.launchpads');
    Route::get('/{launchpad:id}', [Application\Launchpads\LaunchpadController::class, 'view'])->name('api.application.launchpads.view');

    // Rocket Management Endpoint
    Route::group(['prefix' => '/{launchpad:id}/rockets'], function () {
        Route::get('/', [Application\Launchpads\RocketController::class, 'index'])->name('api.application.launchpads.rockets');
        Route::get('/{rocket:id}', [Application\Launchpads\RocketController::class, 'view'])->name('api.application.launchpads.rockets.view');
    });
});
