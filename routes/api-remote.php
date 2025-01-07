<?php

use Illuminate\Support\Facades\Route;
use Kubectyl\Http\Controllers\Api\Remote;

// Routes for the Wings daemon.
Route::post('/sftp/auth', Remote\SftpAuthenticationController::class);

Route::get('/servers', [Remote\Servers\ServerDetailsController::class, 'list']);
Route::post('/servers/reset', [Remote\Servers\ServerDetailsController::class, 'resetState']);
Route::post('/activity', Remote\ActivityProcessingController::class);

Route::group(['prefix' => '/servers/{uuid}'], function () {
    Route::get('/', Remote\Servers\ServerDetailsController::class);
    Route::get('/install', [Remote\Servers\ServerInstallController::class, 'index']);
    Route::post('/install', [Remote\Servers\ServerInstallController::class, 'store']);

    Route::get('/transfer/failure', [Remote\Servers\ServerTransferController::class, 'failure']);
    Route::get('/transfer/success', [Remote\Servers\ServerTransferController::class, 'success']);
    Route::post('/transfer/failure', [Remote\Servers\ServerTransferController::class, 'failure']);
    Route::post('/transfer/success', [Remote\Servers\ServerTransferController::class, 'success']);
});

Route::group(['prefix' => '/snapshots'], function () {
    Route::get('/{snapshot}', Remote\Snapshots\SnapshotRemoteUploadController::class);
    Route::post('/{snapshot}', [Remote\Snapshots\SnapshotStatusController::class, 'index']);
    Route::post('/{snapshot}/restore', [Remote\Snapshots\SnapshotStatusController::class, 'restore']);
});
