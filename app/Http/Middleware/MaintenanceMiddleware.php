<?php

namespace Kubectyl\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory;

class MaintenanceMiddleware
{
    /**
     * MaintenanceMiddleware constructor.
     */
    public function __construct(private ResponseFactory $response)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var \Kubectyl\Models\Server $server */
        $server = $request->attributes->get('server');
        $cluster = $server->getRelation('cluster');

        if ($cluster->maintenance_mode) {
            return $this->response->view('errors.maintenance');
        }

        return $next($request);
    }
}
