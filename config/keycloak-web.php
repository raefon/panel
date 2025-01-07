<?php

return [
    /*
     * Keycloak Url
     *
     * Generally https://your-server.com/auth
     */
    'base_url' => env('KEYCLOAK_BASE_URL', 'https://auth.kubectyl.dev'),

    /*
     * Keycloak Realm
     *
     * Default is master
     */
    'realm' => env('KEYCLOAK_REALM', 'kubectyl'),

    /*
     * The Keycloak Server realm public key (string).
     *
     * @see Keycloak >> Realm Settings >> Keys >> RS256 >> Public Key
     */
    'realm_public_key' => env('KEYCLOAK_REALM_PUBLIC_KEY', 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuB0ymn2hbKpSmj7uMFdk7nqOlrGVsyf6OdOjraslBYcBSYL9Aj1tPVn6fRtohWQpcbwWLpEiV5DFvtuJtCxc7odA2gnvpDkaVOBJybEhxSvG7CIwzvCuQKCXcgQIMhR55g9pRJI1MXzIZ4MWtx/9Fx+nSCsp5+vi6EcSKCQDQMFYrapr/6slLM3JjXpZCJK3Z/9iLdi98k2MrGkW+Qs9LPbwtPenCttn/BJ2w1vaLnUSYL6xCaf1dAH0YLBqPtk/fTiviWv/C4r9el+apKXHCLE0EqTQO3XTjSWUFg4HkDt9bGpkVlzqhDKDL3DKcp6Oe9y6Q1w5YOmO7aijKk+BbQIDAQAB'),

    /*
     * Keycloak Client ID
     *
     * @see Keycloak >> Clients >> Installation
     */
    'client_id' => env('KEYCLOAK_CLIENT_ID', 'laravel-keycloak-web-guard'),

    /*
     * Keycloak Client Secret
     *
     * @see Keycloak >> Clients >> Installation
     */
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET', 'ZIvUqfJ35Pli42iFVnsjRN3PWVVJ96hY'),

    /*
     * We can cache the OpenId Configuration
     * The result from /realms/{realm-name}/.well-known/openid-configuration
     *
     * @link https://www.keycloak.org/docs/3.2/securing_apps/topics/oidc/oidc-generic.html
     */
    'cache_openid' => env('KEYCLOAK_CACHE_OPENID', true),

    /*
     * Page to redirect after callback if there's no "intent"
     *
     * @see Vizir\KeycloakWebGuard\Controllers\AuthController::callback()
     */
    'redirect_url' => '/admin',

    /*
     * The routes for authenticate
     *
     * Accept a string as the first parameter of route() or false to disable the route.
     *
     * The routes will receive the name "keycloak.{route}" and login/callback are required.
     * So, if you make it false, you shoul register a named 'keycloak.login' route and extend
     * the Vizir\KeycloakWebGuard\Controllers\AuthController controller.
     */
    'routes' => [
        'login' => 'login',
        'logout' => 'logout',
        'register' => 'register',
        'callback' => 'callback',
    ],

    /*
     * GuzzleHttp Client options
     *
     * @link http://docs.guzzlephp.org/en/stable/request-options.html
     */
   'guzzle_options' => [],
];
