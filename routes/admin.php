<?php

use Kubectyl\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;
use Kubectyl\Http\Middleware\Admin\Servers\ServerInstalled;

Route::get('/', [Admin\BaseController::class, 'index'])->name('admin.index');

/*
|--------------------------------------------------------------------------
| Location Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /admin/api
|
*/
Route::group(['prefix' => 'api'], function () {
    Route::get('/', [Admin\ApiController::class, 'index'])->name('admin.api.index');
    Route::get('/new', [Admin\ApiController::class, 'create'])->name('admin.api.new');

    Route::post('/new', [Admin\ApiController::class, 'store']);

    Route::delete('/revoke/{identifier}', [Admin\ApiController::class, 'delete'])->name('admin.api.delete');
});

/*
|--------------------------------------------------------------------------
| Location Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /admin/locations
|
*/
Route::group(['prefix' => 'locations'], function () {
    Route::get('/', [Admin\LocationController::class, 'index'])->name('admin.locations');
    Route::get('/view/{location:id}', [Admin\LocationController::class, 'view'])->name('admin.locations.view');

    Route::post('/', [Admin\LocationController::class, 'create']);
    Route::patch('/view/{location:id}', [Admin\LocationController::class, 'update']);
});

/*
|--------------------------------------------------------------------------
| Database Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /admin/databases
|
*/
Route::group(['prefix' => 'databases'], function () {
    Route::get('/', [Admin\DatabaseController::class, 'index'])->name('admin.databases');
    Route::get('/view/{host:id}', [Admin\DatabaseController::class, 'view'])->name('admin.databases.view');

    Route::post('/', [Admin\DatabaseController::class, 'create']);
    Route::patch('/view/{host:id}', [Admin\DatabaseController::class, 'update']);
    Route::delete('/view/{host:id}', [Admin\DatabaseController::class, 'delete']);
});

/*
|--------------------------------------------------------------------------
| Settings Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /admin/settings
|
*/
Route::group(['prefix' => 'settings'], function () {
    Route::get('/', [Admin\Settings\IndexController::class, 'index'])->name('admin.settings');
    Route::get('/mail', [Admin\Settings\MailController::class, 'index'])->name('admin.settings.mail');
    Route::get('/advanced', [Admin\Settings\AdvancedController::class, 'index'])->name('admin.settings.advanced');

    Route::post('/mail/test', [Admin\Settings\MailController::class, 'test'])->name('admin.settings.mail.test');

    Route::patch('/', [Admin\Settings\IndexController::class, 'update']);
    Route::patch('/mail', [Admin\Settings\MailController::class, 'update']);
    Route::patch('/advanced', [Admin\Settings\AdvancedController::class, 'update']);
});

/*
|--------------------------------------------------------------------------
| User Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /admin/users
|
*/
Route::group(['prefix' => 'users'], function () {
    Route::get('/', [Admin\UserController::class, 'index'])->name('admin.users');
    Route::get('/accounts.json', [Admin\UserController::class, 'json'])->name('admin.users.json');
    Route::get('/new', [Admin\UserController::class, 'create'])->name('admin.users.new');
    Route::get('/view/{user:id}', [Admin\UserController::class, 'view'])->name('admin.users.view');

    Route::post('/new', [Admin\UserController::class, 'store']);

    Route::patch('/view/{user:id}', [Admin\UserController::class, 'update']);
    Route::delete('/view/{user:id}', [Admin\UserController::class, 'delete']);
});

/*
|--------------------------------------------------------------------------
| Server Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /admin/servers
|
*/
Route::group(['prefix' => 'servers'], function () {
    Route::get('/', [Admin\Servers\ServerController::class, 'index'])->name('admin.servers');
    Route::get('/new', [Admin\Servers\CreateServerController::class, 'index'])->name('admin.servers.new');
    Route::get('/view/{server:id}', [Admin\Servers\ServerViewController::class, 'index'])->name('admin.servers.view');

    Route::group(['middleware' => [ServerInstalled::class]], function () {
        Route::get('/view/{server:id}/details', [Admin\Servers\ServerViewController::class, 'details'])->name('admin.servers.view.details');
        Route::get('/view/{server:id}/build', [Admin\Servers\ServerViewController::class, 'build'])->name('admin.servers.view.build');
        Route::get('/view/{server:id}/startup', [Admin\Servers\ServerViewController::class, 'startup'])->name('admin.servers.view.startup');
        Route::get('/view/{server:id}/database', [Admin\Servers\ServerViewController::class, 'database'])->name('admin.servers.view.database');
        Route::get('/view/{server:id}/mounts', [Admin\Servers\ServerViewController::class, 'mounts'])->name('admin.servers.view.mounts');
    });

    Route::get('/view/{server:id}/manage', [Admin\Servers\ServerViewController::class, 'manage'])->name('admin.servers.view.manage');
    Route::get('/view/{server:id}/delete', [Admin\Servers\ServerViewController::class, 'delete'])->name('admin.servers.view.delete');

    Route::post('/new', [Admin\Servers\CreateServerController::class, 'store']);
    Route::post('/view/{server:id}/build', [Admin\ServersController::class, 'updateBuild']);
    Route::post('/view/{server:id}/startup', [Admin\ServersController::class, 'saveStartup']);
    Route::post('/view/{server:id}/database', [Admin\ServersController::class, 'newDatabase']);
    Route::post('/view/{server:id}/mounts', [Admin\ServersController::class, 'addMount'])->name('admin.servers.view.mounts.store');
    Route::post('/view/{server:id}/manage/toggle', [Admin\ServersController::class, 'toggleInstall'])->name('admin.servers.view.manage.toggle');
    Route::post('/view/{server:id}/manage/suspension', [Admin\ServersController::class, 'manageSuspension'])->name('admin.servers.view.manage.suspension');
    Route::post('/view/{server:id}/manage/reinstall', [Admin\ServersController::class, 'reinstallServer'])->name('admin.servers.view.manage.reinstall');
    Route::post('/view/{server:id}/manage/transfer', [Admin\Servers\ServerTransferController::class, 'transfer'])->name('admin.servers.view.manage.transfer');
    Route::post('/view/{server:id}/delete', [Admin\ServersController::class, 'delete']);

    Route::patch('/view/{server:id}/details', [Admin\ServersController::class, 'setDetails']);
    Route::patch('/view/{server:id}/database', [Admin\ServersController::class, 'resetDatabasePassword']);

    Route::delete('/view/{server:id}/database/{database:id}/delete', [Admin\ServersController::class, 'deleteDatabase'])->name('admin.servers.view.database.delete');
    Route::delete('/view/{server:id}/mounts/{mount:id}', [Admin\ServersController::class, 'deleteMount'])
        ->name('admin.servers.view.mounts.delete');
});

/*
|--------------------------------------------------------------------------
| Cluster Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /admin/clusters
|
*/
Route::group(['prefix' => 'clusters'], function () {
    Route::get('/', [Admin\Clusters\ClusterController::class, 'index'])->name('admin.clusters');
    Route::get('/new', [Admin\ClustersController::class, 'create'])->name('admin.clusters.new');
    Route::get('/view/{cluster:id}', [Admin\Clusters\ClusterViewController::class, 'index'])->name('admin.clusters.view');
    Route::get('/view/{cluster:id}/settings', [Admin\Clusters\ClusterViewController::class, 'settings'])->name('admin.clusters.view.settings');
    Route::get('/view/{cluster:id}/configuration', [Admin\Clusters\ClusterViewController::class, 'configuration'])->name('admin.clusters.view.configuration');
    Route::get('/view/{cluster:id}/allocation', [Admin\Clusters\ClusterViewController::class, 'allocations'])->name('admin.clusters.view.allocation');
    Route::get('/view/{cluster:id}/servers', [Admin\Clusters\ClusterViewController::class, 'servers'])->name('admin.clusters.view.servers');
    Route::get('/view/{cluster:id}/system-information', Admin\Clusters\SystemInformationController::class);

    Route::post('/new', [Admin\ClustersController::class, 'store']);
    Route::post('/view/{cluster:id}/allocation', [Admin\ClustersController::class, 'createAllocation']);
    Route::post('/view/{cluster:id}/allocation/remove', [Admin\ClustersController::class, 'allocationRemoveBlock'])->name('admin.clusters.view.allocation.removeBlock');
    Route::post('/view/{cluster:id}/allocation/alias', [Admin\ClustersController::class, 'allocationSetAlias'])->name('admin.clusters.view.allocation.setAlias');
    Route::post('/view/{cluster:id}/settings/token', Admin\ClusterAutoDeployController::class)->name('admin.clusters.view.configuration.token');

    Route::patch('/view/{cluster:id}/settings', [Admin\ClustersController::class, 'updateSettings']);

    Route::delete('/view/{cluster:id}/delete', [Admin\ClustersController::class, 'delete'])->name('admin.clusters.view.delete');
    Route::delete('/view/{cluster:id}/allocation/remove/{allocation:id}', [Admin\ClustersController::class, 'allocationRemoveSingle'])->name('admin.clusters.view.allocation.removeSingle');
    Route::delete('/view/{cluster:id}/allocations', [Admin\ClustersController::class, 'allocationRemoveMultiple'])->name('admin.clusters.view.allocation.removeMultiple');
});

/*
|--------------------------------------------------------------------------
| Mount Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /admin/mounts
|
*/
Route::group(['prefix' => 'mounts'], function () {
    Route::get('/', [Admin\MountController::class, 'index'])->name('admin.mounts');
    Route::get('/view/{mount:id}', [Admin\MountController::class, 'view'])->name('admin.mounts.view');

    Route::post('/', [Admin\MountController::class, 'create']);
    Route::post('/{mount:id}/rockets', [Admin\MountController::class, 'addRockets'])->name('admin.mounts.rockets');
    Route::post('/{mount:id}/clusters', [Admin\MountController::class, 'addClusters'])->name('admin.mounts.clusters');

    Route::patch('/view/{mount:id}', [Admin\MountController::class, 'update']);

    Route::delete('/{mount:id}/rockets/{rocket_id}', [Admin\MountController::class, 'deleteRocket']);
    Route::delete('/{mount:id}/clusters/{cluster_id}', [Admin\MountController::class, 'deleteCluster']);
});

/*
|--------------------------------------------------------------------------
| Nest Controller Routes
|--------------------------------------------------------------------------
|
| Endpoint: /admin/launchpads
|
*/
Route::group(['prefix' => 'launchpads'], function () {
    Route::get('/', [Admin\Launchpads\LaunchpadController::class, 'index'])->name('admin.launchpads');
    Route::get('/new', [Admin\Launchpads\LaunchpadController::class, 'create'])->name('admin.launchpads.new');
    Route::get('/view/{launchpad:id}', [Admin\Launchpads\LaunchpadController::class, 'view'])->name('admin.launchpads.view');
    Route::get('/rocket/new', [Admin\Launchpads\RocketController::class, 'create'])->name('admin.launchpads.rocket.new');
    Route::get('/rocket/{rocket:id}', [Admin\Launchpads\RocketController::class, 'view'])->name('admin.launchpads.rocket.view');
    Route::get('/rocket/{rocket:id}/export', [Admin\Launchpads\RocketShareController::class, 'export'])->name('admin.launchpads.rocket.export');
    Route::get('/rocket/{rocket:id}/variables', [Admin\Launchpads\RocketVariableController::class, 'view'])->name('admin.launchpads.rocket.variables');
    Route::get('/rocket/{rocket:id}/scripts', [Admin\Launchpads\RocketScriptController::class, 'index'])->name('admin.launchpads.rocket.scripts');

    Route::post('/new', [Admin\Launchpads\LaunchpadController::class, 'store']);
    Route::post('/import', [Admin\Launchpads\RocketShareController::class, 'import'])->name('admin.launchpads.rocket.import');
    Route::post('/rocket/new', [Admin\Launchpads\RocketController::class, 'store']);
    Route::post('/rocket/{rocket:id}/variables', [Admin\Launchpads\RocketVariableController::class, 'store']);

    Route::put('/rocket/{rocket:id}', [Admin\Launchpads\RocketShareController::class, 'update']);

    Route::patch('/view/{launchpad:id}', [Admin\Launchpads\LaunchpadController::class, 'update']);
    Route::patch('/rocket/{rocket:id}', [Admin\Launchpads\RocketController::class, 'update']);
    Route::patch('/rocket/{rocket:id}/scripts', [Admin\Launchpads\RocketScriptController::class, 'update']);
    Route::patch('/rocket/{rocket:id}/variables/{variable:id}', [Admin\Launchpads\RocketVariableController::class, 'update'])->name('admin.launchpads.rocket.variables.edit');

    Route::delete('/view/{launchpad:id}', [Admin\Launchpads\LaunchpadController::class, 'destroy']);
    Route::delete('/rocket/{rocket:id}', [Admin\Launchpads\RocketController::class, 'destroy']);
    Route::delete('/rocket/{rocket:id}/variables/{variable:id}', [Admin\Launchpads\RocketVariableController::class, 'destroy']);
});
