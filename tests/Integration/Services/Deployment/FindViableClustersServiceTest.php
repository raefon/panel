<?php

namespace Kubectyl\Tests\Integration\Services\Deployment;

use Kubectyl\Models\Server;
use Kubectyl\Models\Cluster;
use Kubectyl\Models\Database;
use Kubectyl\Models\Location;
use Illuminate\Support\Collection;
use Kubectyl\Tests\Integration\IntegrationTestCase;
use Kubectyl\Services\Deployment\FindViableClustersService;

class FindViableClustersServiceTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Database::query()->delete();
        Server::query()->delete();
        Cluster::query()->delete();
    }

    /**
     * Ensure that errors are not thrown back when passing in expected values.
     *
     * @see https://github.com/pterodactyl/panel/issues/2529
     */
    public function testNoExceptionIsThrownIfStringifiedIntegersArePassedForLocations()
    {
        $this->getService()->setLocations([1, 2, 3]);
        $this->getService()->setLocations(['1', '2', '3']);
        $this->getService()->setLocations(['1', 2, 3]);

        try {
            $this->getService()->setLocations(['a']);
            $this->fail('This expectation should not be called.');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
            $this->assertSame('An array of location IDs should be provided when calling setLocations.', $exception->getMessage());
        }

        try {
            $this->getService()->setLocations(['1.2', '1', 2]);
            $this->fail('This expectation should not be called.');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
            $this->assertSame('An array of location IDs should be provided when calling setLocations.', $exception->getMessage());
        }
    }

    public function testExpectedClusterIsReturnedForLocation()
    {
        /** @var \Kubectyl\Models\Location[] $locations */
        $locations = Location::factory()->times(2)->create();

        /** @var \Kubectyl\Models\Cluster[] $clusters */
        $clusters = [
            // This cluster should never be returned once we've completed the initial test which
            // runs without a location filter.
            Cluster::factory()->create([
                'location_id' => $locations[0]->id,
            ]),
            Cluster::factory()->create([
                'location_id' => $locations[1]->id,
            ]),
            Cluster::factory()->create([
                'location_id' => $locations[1]->id,
            ]),
        ];

        // Expect that all the clusters are returned as we're under all of their limits
        // and there is no location filter being provided.
        $response = $this->getService()->handle();
        $this->assertInstanceOf(Collection::class, $response);
        $this->assertCount(3, $response);
        $this->assertInstanceOf(Cluster::class, $response[0]);
    }

    private function getService(): FindViableClustersService
    {
        return $this->app->make(FindViableClustersService::class);
    }
}
