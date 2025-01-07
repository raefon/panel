<?php

namespace Kubectyl\Tests\Traits\Integration;

use Ramsey\Uuid\Uuid;
use Kubectyl\Models\User;
use Kubectyl\Models\Rocket;
use Kubectyl\Models\Server;
use Kubectyl\Models\Cluster;
use Kubectyl\Models\Subuser;
use Kubectyl\Models\Location;
use Kubectyl\Models\Allocation;

trait CreatesTestModels
{
    /**
     * Creates a server model in the databases for the purpose of testing. If an attribute
     * is passed in that normally requires this function to create a model no model will be
     * created and that attribute's value will be used.
     *
     * The returned server model will have all the relationships loaded onto it.
     */
    public function createServerModel(array $attributes = []): Server
    {
        if (isset($attributes['user_id'])) {
            $attributes['owner_id'] = $attributes['user_id'];
        }

        if (!isset($attributes['owner_id'])) {
            /** @var \Kubectyl\Models\User $user */
            $user = User::factory()->create();
            $attributes['owner_id'] = $user->id;
        }

        if (!isset($attributes['cluster_id'])) {
            if (!isset($attributes['location_id'])) {
                /** @var \Kubectyl\Models\Location $location */
                $location = Location::factory()->create();
                $attributes['location_id'] = $location->id;
            }

            /** @var \Kubectyl\Models\Cluster $cluster */
            $cluster = Cluster::factory()->create(['location_id' => $attributes['location_id']]);
            $attributes['cluster_id'] = $cluster->id;
        }

        if (!isset($attributes['allocation_id'])) {
            /** @var \Kubectyl\Models\Allocation $allocation */
            $allocation = Allocation::factory()->create(['cluster_id' => $attributes['cluster_id']]);
            $attributes['allocation_id'] = $allocation->id;
        }

        if (empty($attributes['rocket_id'])) {
            $rocket = !empty($attributes['launchpad_id'])
                ? Rocket::query()->where('launchpad_id', $attributes['launchpad_id'])->firstOrFail()
                : $this->getBungeecordRocket();

            $attributes['rocket_id'] = $rocket->id;
            $attributes['launchpad_id'] = $rocket->launchpad_id;
        }

        if (empty($attributes['launchpad_id'])) {
            $attributes['launchpad_id'] = Rocket::query()->findOrFail($attributes['rocket_id'])->launchpad_id;
        }

        unset($attributes['user_id'], $attributes['location_id']);

        /** @var \Kubectyl\Models\Server $server */
        $server = Server::factory()->create($attributes);

        Allocation::query()->where('id', $server->allocation_id)->update(['server_id' => $server->id]);

        return $server->fresh([
            'location', 'user', 'cluster', 'allocation', 'launchpad', 'rocket',
        ]);
    }

    /**
     * Generates a user and a server for that user. If an array of permissions is passed it
     * is assumed that the user is actually a subuser of the server.
     *
     * @param string[] $permissions
     *
     * @return array{\Kubectyl\Models\User, \Kubectyl\Models\Server}
     */
    public function generateTestAccount(array $permissions = []): array
    {
        /** @var \Kubectyl\Models\User $user */
        $user = User::factory()->create();

        if (empty($permissions)) {
            return [$user, $this->createServerModel(['user_id' => $user->id])];
        }

        $server = $this->createServerModel();

        Subuser::query()->create([
            'user_id' => $user->id,
            'server_id' => $server->id,
            'permissions' => $permissions,
        ]);

        return [$user, $server];
    }

    /**
     * Clones a given rocket allowing us to make modifications that don't affect other
     * tests that rely on the rocket existing in the correct state.
     */
    protected function cloneRocketAndVariables(Rocket $rocket): Rocket
    {
        $model = $rocket->replicate(['id', 'uuid']);
        $model->uuid = Uuid::uuid4()->toString();
        $model->push();

        /** @var \Kubectyl\Models\Rocket $model */
        $model = $model->fresh();

        foreach ($rocket->variables as $variable) {
            $variable->replicate(['id', 'rocket_id'])->forceFill(['rocket_id' => $model->id])->push();
        }

        return $model->fresh();
    }

    /**
     * Almost every test just assumes it is using BungeeCord — this is the critical
     * rocket model for all tests unless specified otherwise.
     */
    private function getBungeecordRocket(): Rocket
    {
        /** @var \Kubectyl\Models\Rocket $rocket */
        $rocket = Rocket::query()->where('author', 'support@kubectyl.org')->where('name', 'Bungeecord')->firstOrFail();

        return $rocket;
    }
}
