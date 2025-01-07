<?php

namespace Kubectyl\Repositories\Eloquent;

use Kubectyl\Models\Launchpad;
use Illuminate\Database\Eloquent\Collection;
use Kubectyl\Exceptions\Repository\RecordNotFoundException;
use Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface;

class LaunchpadRepository extends EloquentRepository implements LaunchpadRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return Launchpad::class;
    }

    /**
     * Return a launchpad or all launchpads with their associated rockets and variables.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithRockets(int $id = null): Collection|Launchpad
    {
        $instance = $this->getBuilder()->with('rockets', 'rockets.variables');

        if (!is_null($id)) {
            $instance = $instance->find($id, $this->getColumns());
            if (!$instance) {
                throw new RecordNotFoundException();
            }

            return $instance;
        }

        return $instance->get($this->getColumns());
    }

    /**
     * Return a launchpad or all launchpads and the count of rockets and servers for that launchpad.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithCounts(int $id = null): Collection|Launchpad
    {
        $instance = $this->getBuilder()->withCount(['rockets', 'servers']);

        if (!is_null($id)) {
            $instance = $instance->find($id, $this->getColumns());
            if (!$instance) {
                throw new RecordNotFoundException();
            }

            return $instance;
        }

        return $instance->get($this->getColumns());
    }

    /**
     * Return a launchpad along with its associated rockets and the servers relation on those rockets.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithRocketServers(int $id): Launchpad
    {
        $instance = $this->getBuilder()->with('rockets.servers')->find($id, $this->getColumns());
        if (!$instance) {
            throw new RecordNotFoundException();
        }

        /* @var Launchpad $instance */
        return $instance;
    }
}
