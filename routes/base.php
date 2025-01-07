<?php

use Kubectyl\Http\Controllers\Base;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
// use Kubectyl\Http\Controllers\Auth;
use Kubectyl\Http\Middleware\RequireTwoFactorAuthentication;

Route::get('/keycloak', function () {
    $credentials = KeycloakWeb::retrieveToken();
    if (empty($credentials)) {
        return false;
    }

    $user = KeycloakWeb::getUserProfile($credentials);

    return $user;
})->name('keycloak');

Route::get('/protected', function () {
    return Auth::roles();
    // return KeycloakWeb::retrieveToken();
    // return 'This is a protected route!';
});

// Route::get('/callback', [Auth\AuthController::class, 'callback'])->name('keycloak.callback');

Route::get('/', [Base\IndexController::class, 'index'])->name('index')->fallback();
Route::get('/account', [Base\IndexController::class, 'index'])
    ->withoutMiddleware(RequireTwoFactorAuthentication::class)
    ->name('account');

Route::get('/locales/locale.json', Base\LocaleController::class)
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->where('namespace', '.*');

Route::get('/{react}', [Base\IndexController::class, 'index'])
    ->where('react', '^(?!(\/)?(api|auth|admin|daemon)).+');
