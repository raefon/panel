<?php

namespace Kubectyl\Contracts\Repository;

use Kubectyl\Models\Launchpad;
use Illuminate\Database\Eloquent\Collection;

interface LaunchpadRepositoryInterface extends RepositoryInterface
{
    /**
     * Return a launchpad or all launchpads with their associated rockets and variables.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithRockets(int $id = null): Collection|Launchpad;

    /**
     * Return a launchpad or all launchpads and the count of rockets and servers for that launchpad.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithCounts(int $id = null): Collection|Launchpad;

    /**
     * Return a launchpad along with its associated rockets and the servers relation on those rockets.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithRocketServers(int $id): Launchpad;
}
