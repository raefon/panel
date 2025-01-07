<?php

namespace Kubectyl\Tests\Integration\Services\Allocations;

use Kubectyl\Models\Allocation;
use Kubectyl\Tests\Integration\IntegrationTestCase;
use Kubectyl\Services\Allocations\FindAssignableAllocationService;
use Kubectyl\Exceptions\Service\Allocation\AutoAllocationNotEnabledException;
use Kubectyl\Exceptions\Service\Allocation\NoAutoAllocationSpaceAvailableException;

class FindAssignableAllocationServiceTest extends IntegrationTestCase
{
    /**
     * Setup tests.
     */
    public function setUp(): void
    {
        parent::setUp();

        config()->set('kubectyl.client_features.allocations.enabled', true);
        config()->set('kubectyl.client_features.allocations.range_start', 0);
        config()->set('kubectyl.client_features.allocations.range_end', 0);
    }

    /**
     * Test that an unassigned allocation is preferred rather than creating an entirely new
     * allocation for the server.
     */
    public function testExistingAllocationIsPreferred()
    {
        $server = $this->createServerModel();

        $created = Allocation::factory()->create([
            'cluster_id' => $server->cluster_id,
            'ip' => $server->allocation->ip,
        ]);

        $response = $this->getService()->handle($server);

        $this->assertSame($created->id, $response->id);
        $this->assertSame($server->allocation->ip, $response->ip);
        $this->assertSame($server->cluster_id, $response->cluster_id);
        $this->assertSame($server->id, $response->server_id);
        $this->assertNotSame($server->allocation_id, $response->id);
    }

    /**
     * Test that a new allocation is created if there is not a free one available.
     */
    public function testNewAllocationIsCreatedIfOneIsNotFound()
    {
        $server = $this->createServerModel();
        config()->set('kubectyl.client_features.allocations.range_start', 5000);
        config()->set('kubectyl.client_features.allocations.range_end', 5005);

        $response = $this->getService()->handle($server);
        $this->assertSame($server->id, $response->server_id);
        $this->assertSame($server->allocation->ip, $response->ip);
        $this->assertSame($server->cluster_id, $response->cluster_id);
        $this->assertNotSame($server->allocation_id, $response->id);
        $this->assertTrue($response->port >= 5000 && $response->port <= 5005);
    }

    /**
     * Test that a currently assigned port is never assigned to a server.
     */
    public function testOnlyPortNotInUseIsCreated()
    {
        $server = $this->createServerModel();
        $server2 = $this->createServerModel(['cluster_id' => $server->cluster_id]);

        config()->set('kubectyl.client_features.allocations.range_start', 5000);
        config()->set('kubectyl.client_features.allocations.range_end', 5001);

        Allocation::factory()->create([
            'server_id' => $server2->id,
            'cluster_id' => $server->cluster_id,
            'ip' => $server->allocation->ip,
            'port' => 5000,
        ]);

        $response = $this->getService()->handle($server);
        $this->assertSame(5001, $response->port);
    }

    public function testExceptionIsThrownIfNoMoreAllocationsCanBeCreatedInRange()
    {
        $server = $this->createServerModel();
        $server2 = $this->createServerModel(['cluster_id' => $server->cluster_id]);
        config()->set('kubectyl.client_features.allocations.range_start', 5000);
        config()->set('kubectyl.client_features.allocations.range_end', 5005);

        for ($i = 5000; $i <= 5005; ++$i) {
            Allocation::factory()->create([
                'ip' => $server->allocation->ip,
                'port' => $i,
                'cluster_id' => $server->cluster_id,
                'server_id' => $server2->id,
            ]);
        }

        $this->expectException(NoAutoAllocationSpaceAvailableException::class);
        $this->expectExceptionMessage('Cannot assign additional allocation: no more space available on cluster.');

        $this->getService()->handle($server);
    }

    /**
     * Test that we only auto-allocate from the current server's IP address space, and not a random
     * IP address available on that cluster.
     */
    public function testExceptionIsThrownIfOnlyFreePortIsOnADifferentIp()
    {
        $server = $this->createServerModel();

        Allocation::factory()->times(5)->create(['cluster_id' => $server->cluster_id]);

        $this->expectException(NoAutoAllocationSpaceAvailableException::class);
        $this->expectExceptionMessage('Cannot assign additional allocation: no more space available on cluster.');

        $this->getService()->handle($server);
    }

    public function testExceptionIsThrownIfStartOrEndRangeIsNotDefined()
    {
        $server = $this->createServerModel();

        $this->expectException(NoAutoAllocationSpaceAvailableException::class);
        $this->expectExceptionMessage('Cannot assign additional allocation: no more space available on cluster.');

        $this->getService()->handle($server);
    }

    public function testExceptionIsThrownIfStartOrEndRangeIsNotNumeric()
    {
        $server = $this->createServerModel();
        config()->set('kubectyl.client_features.allocations.range_start', 'hodor');
        config()->set('kubectyl.client_features.allocations.range_end', 10);

        try {
            $this->getService()->handle($server);
            $this->fail('This assertion should not be reached.');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
            $this->assertSame('Expected an integerish value. Got: string', $exception->getMessage());
        }

        config()->set('kubectyl.client_features.allocations.range_start', 10);
        config()->set('kubectyl.client_features.allocations.range_end', 'hodor');

        try {
            $this->getService()->handle($server);
            $this->fail('This assertion should not be reached.');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
            $this->assertSame('Expected an integerish value. Got: string', $exception->getMessage());
        }
    }

    public function testExceptionIsThrownIfFeatureIsNotEnabled()
    {
        config()->set('kubectyl.client_features.allocations.enabled', false);
        $server = $this->createServerModel();

        $this->expectException(AutoAllocationNotEnabledException::class);

        $this->getService()->handle($server);
    }

    private function getService(): FindAssignableAllocationService
    {
        return $this->app->make(FindAssignableAllocationService::class);
    }
}
