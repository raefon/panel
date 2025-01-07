<?php

namespace Kubectyl\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vizir\KeycloakWebGuard\Facades\KeycloakWeb;
use Vizir\KeycloakWebGuard\Exceptions\KeycloakCallbackException;
use Vizir\KeycloakWebGuard\Controllers\AuthController as KeycloakAuthController;

class AuthController extends KeycloakAuthController
{
    /**
     * Keycloak callback page.
     *
     * @return view
     *
     * @throws KeycloakCallbackException
     */
    public function callback(Request $request)
    {
        // Check for errors from Keycloak
        if (!empty($request->input('error'))) {
            $error = $request->input('error_description');
            $error = ($error) ?: $request->input('error');

            throw new KeycloakCallbackException($error);
        }

        // Check given state to mitigate CSRF attack
        $state = $request->input('state');
        if (empty($state) || !KeycloakWeb::validateState($state)) {
            KeycloakWeb::forgetState();

            throw new KeycloakCallbackException('Invalid state');
        }

        // Change code for token
        $code = $request->input('code');
        if (!empty($code)) {
            $token = KeycloakWeb::getAccessToken($code);

            // $credentials = KeycloakWeb::retrieveToken();
            // print_r(KeycloakWeb::getUserProfile($credentials));
            // die;

            // print_r($credentials);
            // die;

            if (Auth::validate($token)) {
                $url = config('keycloak-web.redirect_url', '/admin');

                return redirect()->intended($url);
            }
        }

        return redirect(route('keycloak.login'));
    }

    public function validate2(array $credentials = [])
    {
        if (empty($credentials['access_token']) || empty($credentials['id_token'])) {
            return false;
        }

        /*
         * Store the section
         */
        $credentials['refresh_token'] = $credentials['refresh_token'] ?? '';
        KeycloakWeb::saveToken($credentials);

        return Auth::authenticate();
        // return $this->authenticate();
    }

    /**
     * Try to authenticate the user.
     *
     * @return bool
     *
     * @throws KeycloakCallbackException
     */
    public function authenticate()
    {
        // Get Credentials
        $credentials = KeycloakWeb::retrieveToken();
        if (empty($credentials)) {
            return false;
        }

        $user = KeycloakWeb::getUserProfile($credentials);
        if (empty($user)) {
            KeycloakWeb::forgetToken();

            if (Config::get('app.debug', false)) {
                throw new KeycloakCallbackException('User cannot be authenticated.');
            }

            return false;
        }

        // Provide User
        $provider = new KeycloakWebUserProvider('Kubectyl\Models\User');
        $user = $provider->retrieveByCredentials($user);

        Auth::setUser($user);

        return true;
    }
}
