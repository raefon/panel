<?php

namespace Kubectyl\Services\Rockets;

use Kubectyl\Exceptions\Service\HasActiveServersException;
use Kubectyl\Contracts\Repository\RocketRepositoryInterface;
use Kubectyl\Contracts\Repository\ServerRepositoryInterface;
use Kubectyl\Exceptions\Service\Rocket\HasChildrenException;

class RocketDeletionService
{
    /**
     * RocketDeletionService constructor.
     */
    public function __construct(
        protected ServerRepositoryInterface $serverRepository,
        protected RocketRepositoryInterface $repository
    ) {
    }

    /**
     * Delete an Rocket from the database if it has no active servers attached to it.
     *
     * @throws \Kubectyl\Exceptions\Service\HasActiveServersException
     * @throws \Kubectyl\Exceptions\Service\Rocket\HasChildrenException
     */
    public function handle(int $rocket): int
    {
        $servers = $this->serverRepository->findCountWhere([['rocket_id', '=', $rocket]]);
        if ($servers > 0) {
            throw new HasActiveServersException(trans('exceptions.launchpad.rocket.delete_has_servers'));
        }

        $children = $this->repository->findCountWhere([['config_from', '=', $rocket]]);
        if ($children > 0) {
            throw new HasChildrenException(trans('exceptions.launchpad.rocket.has_children'));
        }

        return $this->repository->delete($rocket);
    }
}
