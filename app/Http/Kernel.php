<?php

namespace Kubectyl\Http;

use Illuminate\Auth\Middleware\Authorize;
use Kubectyl\Http\Middleware\TrimStrings;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Middleware\TrustProxies;
use Kubectyl\Http\Middleware\EncryptCookies;
use Kubectyl\Http\Middleware\Api\IsValidJson;
use Kubectyl\Http\Middleware\VerifyCsrfToken;
use Kubectyl\Http\Middleware\VerifyReCaptcha;
use Illuminate\Session\Middleware\StartSession;
use Kubectyl\Http\Middleware\LanguageMiddleware;
use Kubectyl\Http\Middleware\Activity\TrackAPIKey;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Kubectyl\Http\Middleware\MaintenanceMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Kubectyl\Http\Middleware\EnsureStatefulRequests;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Kubectyl\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Kubectyl\Http\Middleware\Api\AuthenticateIPAccess;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Kubectyl\Http\Middleware\Api\Daemon\DaemonAuthenticate;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Kubectyl\Http\Middleware\Api\Client\RequireClientApiKey;
use Kubectyl\Http\Middleware\RequireTwoFactorAuthentication;
use Kubectyl\Http\Middleware\Api\Client\SubstituteClientBindings;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Kubectyl\Http\Middleware\Api\Application\AuthenticateApplicationUser;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     */
    protected $middleware = [
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            LanguageMiddleware::class,
        ],
        'api' => [
            EnsureStatefulRequests::class,
            'auth:sanctum',
            IsValidJson::class,
            TrackAPIKey::class,
            RequireTwoFactorAuthentication::class,
            AuthenticateIPAccess::class,
        ],
        'application-api' => [
            SubstituteBindings::class,
            AuthenticateApplicationUser::class,
        ],
        'client-api' => [
            SubstituteClientBindings::class,
            RequireClientApiKey::class,
        ],
        'daemon' => [
            SubstituteBindings::class,
            DaemonAuthenticate::class,
        ],
    ];

    /**
     * The application's route middleware.
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'auth.session' => AuthenticateSession::class,
        'guest' => RedirectIfAuthenticated::class,
        'csrf' => VerifyCsrfToken::class,
        'throttle' => ThrottleRequests::class,
        'can' => Authorize::class,
        'bindings' => SubstituteBindings::class,
        'recaptcha' => VerifyReCaptcha::class,
        'cluster.maintenance' => MaintenanceMiddleware::class,
    ];
}
