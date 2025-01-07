<?php

namespace Kubectyl\Tests\Integration\Api\Client\Server\Snapshot;

use Carbon\CarbonImmutable;
use Kubectyl\Models\Subuser;
use Kubectyl\Models\Snapshot;
use Kubectyl\Services\Snapshots\DeleteSnapshotService;
use Kubectyl\Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class SnapshotAuthorizationTest extends ClientApiIntegrationTestCase
{
    /**
     * @dataProvider methodDataProvider
     */
    public function testAccessToAServersSnapshotIsRestrictedProperly(string $method, string $endpoint)
    {
        // The API $user is the owner of $server1.
        [$user, $server1] = $this->generateTestAccount();
        // Will be a subuser of $server2.
        $server2 = $this->createServerModel();
        // And as no access to $server3.
        $server3 = $this->createServerModel();

        // Set the API $user as a subuser of server 2, but with no permissions
        // to do anything with the snapshots for that server.
        Subuser::factory()->create(['server_id' => $server2->id, 'user_id' => $user->id]);

        $snapshot1 = Snapshot::factory()->create(['server_id' => $server1->id, 'completed_at' => CarbonImmutable::now()]);
        $snapshot2 = Snapshot::factory()->create(['server_id' => $server2->id, 'completed_at' => CarbonImmutable::now()]);
        $snapshot3 = Snapshot::factory()->create(['server_id' => $server3->id, 'completed_at' => CarbonImmutable::now()]);

        $this->instance(DeleteSnapshotService::class, $mock = \Mockery::mock(DeleteSnapshotService::class));

        if ($method === 'DELETE') {
            $mock->expects('handle')->andReturnUndefined();
        }

        // This is the only valid call for this test, accessing the snapshot for the same
        // server that the API user is the owner of.
        $this->actingAs($user)->json($method, $this->link($server1, '/snapshots/' . $snapshot1->uuid . $endpoint))
            ->assertStatus($method === 'DELETE' ? 204 : 200);

        // This request fails because the snapshot is valid for that server but the user
        // making the request is not authorized to perform that action.
        $this->actingAs($user)->json($method, $this->link($server2, '/snapshots/' . $snapshot2->uuid . $endpoint))->assertForbidden();

        // Both of these should report a 404 error due to the snapshot being linked to
        // servers that are not the same as the server in the request, or are assigned
        // to a server for which the user making the request has no access to.
        $this->actingAs($user)->json($method, $this->link($server1, '/snapshots/' . $snapshot2->uuid . $endpoint))->assertNotFound();
        $this->actingAs($user)->json($method, $this->link($server1, '/snapshots/' . $snapshot3->uuid . $endpoint))->assertNotFound();
        $this->actingAs($user)->json($method, $this->link($server2, '/snapshots/' . $snapshot3->uuid . $endpoint))->assertNotFound();
        $this->actingAs($user)->json($method, $this->link($server3, '/snapshots/' . $snapshot3->uuid . $endpoint))->assertNotFound();
    }

    public function methodDataProvider(): array
    {
        return [
            ['GET', ''],
            ['DELETE', ''],
        ];
    }
}
