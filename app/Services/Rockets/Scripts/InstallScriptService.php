<?php

namespace Kubectyl\Services\Rockets\Scripts;

use Kubectyl\Models\Rocket;
use Kubectyl\Contracts\Repository\RocketRepositoryInterface;
use Kubectyl\Exceptions\Service\Rocket\InvalidCopyFromException;

class InstallScriptService
{
    /**
     * InstallScriptService constructor.
     */
    public function __construct(protected RocketRepositoryInterface $repository)
    {
    }

    /**
     * Modify the install script for a given Rocket.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     * @throws \Kubectyl\Exceptions\Service\Rocket\InvalidCopyFromException
     */
    public function handle(Rocket $rocket, array $data): void
    {
        if (!is_null(array_get($data, 'copy_script_from'))) {
            if (!$this->repository->isCopyableScript(array_get($data, 'copy_script_from'), $rocket->launchpad_id)) {
                throw new InvalidCopyFromException(trans('exceptions.launchpad.rocket.invalid_copy_id'));
            }
        }

        $this->repository->withoutFreshModel()->update($rocket->id, [
            'script_install' => array_get($data, 'script_install'),
            'script_is_privileged' => array_get($data, 'script_is_privileged', 1),
            'script_entry' => array_get($data, 'script_entry'),
            'script_container' => array_get($data, 'script_container'),
            'copy_script_from' => array_get($data, 'copy_script_from'),
        ]);
    }
}
