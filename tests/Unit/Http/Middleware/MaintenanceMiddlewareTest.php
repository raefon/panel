<?php

namespace Kubectyl\Tests\Unit\Http\Middleware;

use Mockery as m;
use Mockery\MockInterface;
use Kubectyl\Models\Server;
use Kubectyl\Models\Cluster;
use Illuminate\Http\Response;
use Illuminate\Contracts\Routing\ResponseFactory;
use Kubectyl\Http\Middleware\MaintenanceMiddleware;

class MaintenanceMiddlewareTest extends MiddlewareTestCase
{
    private MockInterface $response;

    /**
     * Setup tests.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->response = m::mock(ResponseFactory::class);
    }

    /**
     * Test that a cluster not in maintenance mode continues through the request cycle.
     */
    public function testHandle()
    {
        $server = Server::factory()->make();
        $cluster = Cluster::factory()->make(['maintenance' => 0]);

        $server->setRelation('cluster', $cluster);
        $this->setRequestAttribute('server', $server);

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that a cluster in maintenance mode returns an error view.
     */
    public function testHandleInMaintenanceMode()
    {
        $server = Server::factory()->make();
        $cluster = Cluster::factory()->make(['maintenance_mode' => 1]);

        $server->setRelation('cluster', $cluster);
        $this->setRequestAttribute('server', $server);

        $this->response->shouldReceive('view')
            ->once()
            ->with('errors.maintenance')
            ->andReturn(new Response());

        $response = $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
        $this->assertInstanceOf(Response::class, $response);
    }

    private function getMiddleware(): MaintenanceMiddleware
    {
        return new MaintenanceMiddleware($this->response);
    }
}
