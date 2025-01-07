<?php

namespace Kubectyl\Tests\Integration\Api\Client\Server\Snapshot;

use Mockery\MockInterface;
use Illuminate\Http\Response;
use Kubectyl\Models\Snapshot;
use Kubectyl\Models\Permission;
use Kubectyl\Events\ActivityLogged;
use Illuminate\Support\Facades\Event;
use Kubectyl\Repositories\Kuber\DaemonSnapshotRepository;
use Kubectyl\Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class DeleteSnapshotTest extends ClientApiIntegrationTestCase
{
    private MockInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->mock(DaemonSnapshotRepository::class);
    }

    public function testUserWithoutPermissionCannotDeleteSnapshot()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_SNAPSHOT_CREATE]);

        $snapshot = Snapshot::factory()->create(['server_id' => $server->id]);

        $this->actingAs($user)->deleteJson($this->link($snapshot))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * Tests that a snapshot can be deleted for a server and that it is properly updated
     * in the database. Once deleted there should also be a corresponding record in the
     * activity logs table for this API call.
     */
    public function testSnapshotCanBeDeleted()
    {
        Event::fake([ActivityLogged::class]);

        [$user, $server] = $this->generateTestAccount([Permission::ACTION_SNAPSHOT_DELETE]);

        /** @var \Kubectyl\Models\Snapshot $snapshot */
        $snapshot = Snapshot::factory()->create(['server_id' => $server->id]);

        $this->repository->expects('setServer->delete')->with(
            \Mockery::on(function ($value) use ($snapshot) {
                return $value instanceof Snapshot && $value->uuid === $snapshot->uuid;
            })
        )->andReturn(new Response());

        $this->actingAs($user)->deleteJson($this->link($snapshot))->assertStatus(Response::HTTP_NO_CONTENT);

        $snapshot->refresh();
        $this->assertSoftDeleted($snapshot);

        $this->assertActivityFor('server:snapshot.delete', $user, [$snapshot, $snapshot->server]);

        $this->actingAs($user)->deleteJson($this->link($snapshot))->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
