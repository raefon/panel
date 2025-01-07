<?php

namespace Kubectyl\Http\Middleware\Api\Daemon;

use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\Encrypter;
use Kubectyl\Repositories\Eloquent\ClusterRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Kubectyl\Exceptions\Repository\RecordNotFoundException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DaemonAuthenticate
{
    /**
     * Daemon routes that this middleware should be skipped on.
     */
    protected array $except = [
        'daemon.configuration',
    ];

    /**
     * DaemonAuthenticate constructor.
     */
    public function __construct(private Encrypter $encrypter, private ClusterRepository $repository)
    {
    }

    /**
     * Check if a request from the daemon can be properly attributed back to a single cluster instance.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        if (in_array($request->route()->getName(), $this->except)) {
            return $next($request);
        }

        if (is_null($bearer = $request->bearerToken())) {
            throw new HttpException(401, 'Access to this endpoint must include an Authorization header.', null, ['WWW-Authenticate' => 'Bearer']);
        }

        $parts = explode('.', $bearer);
        // Ensure that all of the correct parts are provided in the header.
        if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
            throw new BadRequestHttpException('The Authorization header provided was not in a valid format.');
        }

        try {
            /** @var \Kubectyl\Models\Cluster $cluster */
            $cluster = $this->repository->findFirstWhere([
                'daemon_token_id' => $parts[0],
            ]);

            if (hash_equals((string) $this->encrypter->decrypt($cluster->daemon_token), $parts[1])) {
                $request->attributes->set('cluster', $cluster);

                return $next($request);
            }
        } catch (RecordNotFoundException $exception) {
            // Do nothing, we don't want to expose a cluster not existing at all.
        }

        throw new AccessDeniedHttpException('You are not authorized to access this resource.');
    }
}
