<?php

namespace Kubectyl\Services\Launchpads;

use Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface;

class LaunchpadUpdateService
{
    /**
     * LaunchpadUpdateService constructor.
     */
    public function __construct(protected LaunchpadRepositoryInterface $repository)
    {
    }

    /**
     * Update a launchpad and prevent changing the author once it is set.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(int $launchpad, array $data): void
    {
        if (!is_null(array_get($data, 'author'))) {
            unset($data['author']);
        }

        $this->repository->withoutFreshModel()->update($launchpad, $data);
    }
}
