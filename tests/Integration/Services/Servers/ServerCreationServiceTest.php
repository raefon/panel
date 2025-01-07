<?php

namespace Kubectyl\Tests\Integration\Services\Servers;

use Kubectyl\Models\User;
use Mockery\MockInterface;
use Kubectyl\Models\Rocket;
use Kubectyl\Models\Server;
use GuzzleHttp\Psr7\Request;
use Kubectyl\Models\Cluster;
use GuzzleHttp\Psr7\Response;
use Kubectyl\Models\Location;
use Kubectyl\Models\Allocation;
use Illuminate\Foundation\Testing\WithFaker;
use Kubectyl\Models\Objects\DeploymentObject;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Validation\ValidationException;
use Kubectyl\Tests\Integration\IntegrationTestCase;
use Kubectyl\Services\Servers\ServerCreationService;
use Kubectyl\Repositories\Kuber\DaemonServerRepository;
use Kubectyl\Exceptions\Http\Connection\DaemonConnectionException;

class ServerCreationServiceTest extends IntegrationTestCase
{
    use WithFaker;

    protected MockInterface $daemonServerRepository;

    protected Rocket $bungeecord;

    /**
     * Stub the calls to Kuber so that we don't actually hit those API endpoints.
     */
    public function setUp(): void
    {
        parent::setUp();

        /* @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->bungeecord = Rocket::query()
            ->where('author', 'support@kubectyl.org')
            ->where('name', 'Bungeecord')
            ->firstOrFail();

        $this->daemonServerRepository = \Mockery::mock(DaemonServerRepository::class);
        $this->swap(DaemonServerRepository::class, $this->daemonServerRepository);
    }

    /**
     * Test that a server can be created when a deployment object is provided to the service.
     *
     * This doesn't really do anything super complicated, we'll rely on other more specific
     * tests to cover that the logic being used does indeed find suitable clusters and ports. For
     * this test we just care that it is recognized and passed off to those functions.
     */
    public function testServerIsCreatedWithDeploymentObject()
    {
        /** @var \Kubectyl\Models\User $user */
        $user = User::factory()->create();

        /** @var \Kubectyl\Models\Location $location */
        $location = Location::factory()->create();

        /** @var \Kubectyl\Models\Cluster $cluster */
        $cluster = Cluster::factory()->create([
            'location_id' => $location->id,
        ]);

        /** @var \Kubectyl\Models\Allocation[]|\Illuminate\Database\Eloquent\Collection $allocations */
        $allocations = Allocation::factory()->times(5)->create([
            'cluster_id' => $cluster->id,
        ]);

        $deployment = (new DeploymentObject())->setDedicated(true)->setLocations([$cluster->location_id])->setPorts([
            $allocations[0]->port,
        ]);

        $rocket = $this->cloneRocketAndVariables($this->bungeecord);
        // We want to make sure that the validator service runs as an admin, and not as a regular
        // user when saving variables.
        $rocket->variables()->first()->update([
            'user_editable' => false,
        ]);

        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'owner_id' => $user->id,
            'memory_request' => 256,
            'memory_limit' => 512,
            'disk' => 100,
            'cpu_request' => 13,
            'cpu_limit' => 100,
            'startup' => 'java server2.jar',
            'image' => 'java:8',
            'rocket_id' => $rocket->id,
            'allocation_additional' => [
                $allocations[4]->id,
            ],
            'environment' => [
                'BUNGEE_VERSION' => '123',
                'SERVER_JARFILE' => 'server2.jar',
            ],
            'start_on_completion' => true,
        ];

        $this->daemonServerRepository->expects('setServer->create')->with(true)->andReturnUndefined();

        try {
            $this->getService()->handle(array_merge($data, [
                'environment' => [
                    'BUNGEE_VERSION' => '',
                    'SERVER_JARFILE' => 'server2.jar',
                ],
            ]), $deployment);

            $this->fail('This execution pathway should not be reached.');
        } catch (ValidationException $exception) {
            $this->assertCount(1, $exception->errors());
            $this->assertArrayHasKey('environment.BUNGEE_VERSION', $exception->errors());
            $this->assertSame('The Bungeecord Version variable field is required.', $exception->errors()['environment.BUNGEE_VERSION'][0]);
        }

        $response = $this->getService()->handle($data, $deployment);

        $this->assertInstanceOf(Server::class, $response);
        $this->assertNotNull($response->uuid);
        $this->assertSame($response->uuidShort, substr($response->uuid, 0, 8));
        $this->assertSame($rocket->id, $response->rocket_id);
        $variables = $response->variables->sortBy('server_value')->values();
        $this->assertCount(2, $variables);
        $this->assertSame('123', $variables->get(0)->server_value);
        $this->assertSame('server2.jar', $variables->get(1)->server_value);

        foreach ($data as $key => $value) {
            if (in_array($key, ['allocation_additional', 'environment', 'start_on_completion'])) {
                continue;
            }

            $this->assertSame($value, $response->{$key}, "Failed asserting equality of '$key' in server response. Got: [{$response->{$key}}] Expected: [$value]");
        }

        $this->assertCount(2, $response->allocations);
        $this->assertSame($response->allocation_id, $response->allocations[0]->id);
        $this->assertSame($allocations[0]->id, $response->allocations[0]->id);
        $this->assertSame($allocations[4]->id, $response->allocations[1]->id);

        $this->assertFalse($response->isSuspended());
        $this->assertSame(0, $response->database_limit);
        $this->assertSame(0, $response->allocation_limit);
        $this->assertSame(0, $response->snapshot_limit);
    }

    /**
     * Test that a server is deleted from the Panel if Kuber returns an error during the creation
     * process.
     */
    public function testErrorEncounteredByKuberCausesServerToBeDeleted()
    {
        /** @var \Kubectyl\Models\User $user */
        $user = User::factory()->create();

        /** @var \Kubectyl\Models\Location $location */
        $location = Location::factory()->create();

        /** @var \Kubectyl\Models\Cluster $cluster */
        $cluster = Cluster::factory()->create([
            'location_id' => $location->id,
        ]);

        /** @var \Kubectyl\Models\Allocation $allocation */
        $allocation = Allocation::factory()->create([
            'cluster_id' => $cluster->id,
        ]);

        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'owner_id' => $user->id,
            'allocation_id' => $allocation->id,
            'default_port' => 65535,
            'cluster_id' => $cluster->id,
            'memory_request' => 128,
            'memory_limit' => 1024,
            'disk' => 100,
            'cpu_request' => 25,
            'cpu_limit' => 100,
            'startup' => 'java server2.jar',
            'image' => 'java:8',
            'rocket_id' => $this->bungeecord->id,
            'environment' => [
                'BUNGEE_VERSION' => '123',
                'SERVER_JARFILE' => 'server2.jar',
            ],
        ];

        $this->daemonServerRepository->expects('setServer->create')->andThrows(
            new DaemonConnectionException(
                new BadResponseException('Bad request', new Request('POST', '/create'), new Response(500))
            )
        );

        $this->daemonServerRepository->expects('setServer->delete')->andReturnUndefined();

        $this->expectException(DaemonConnectionException::class);

        $this->getService()->handle($data);

        $this->assertDatabaseMissing('servers', ['owner_id' => $user->id]);
    }

    private function getService(): ServerCreationService
    {
        return $this->app->make(ServerCreationService::class);
    }
}
