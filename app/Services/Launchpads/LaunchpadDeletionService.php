<?php

namespace Kubectyl\Services\Launchpads;

use Kubectyl\Exceptions\Service\HasActiveServersException;
use Kubectyl\Contracts\Repository\ServerRepositoryInterface;
use Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface;

class LaunchpadDeletionService
{
    /**
     * LaunchpadDeletionService constructor.
     */
    public function __construct(
        protected ServerRepositoryInterface $serverRepository,
        protected LaunchpadRepositoryInterface $repository
    ) {
    }

    /**
     * Delete a launchpad from the system only if there are no servers attached to it.
     *
     * @throws \Kubectyl\Exceptions\Service\HasActiveServersException
     */
    public function handle(int $launchpad): int
    {
        $count = $this->serverRepository->findCountWhere([['launchpad_id', '=', $launchpad]]);
        if ($count > 0) {
            throw new HasActiveServersException(trans('exceptions.launchpad.delete_has_servers'));
        }

        return $this->repository->delete($launchpad);
    }
}
