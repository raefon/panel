<?php

namespace Kubectyl\Services\Rockets;

use Kubectyl\Models\Rocket;
use Kubectyl\Contracts\Repository\RocketRepositoryInterface;
use Kubectyl\Exceptions\Service\Rocket\NoParentConfigurationFoundException;

class RocketUpdateService
{
    /**
     * RocketUpdateService constructor.
     */
    public function __construct(protected RocketRepositoryInterface $repository)
    {
    }

    /**
     * Update a service option.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     * @throws \Kubectyl\Exceptions\Service\Rocket\NoParentConfigurationFoundException
     */
    public function handle(Rocket $rocket, array $data): void
    {
        if (!is_null(array_get($data, 'config_from'))) {
            $results = $this->repository->findCountWhere([
                ['launchpad_id', '=', $rocket->launchpad_id],
                ['id', '=', array_get($data, 'config_from')],
            ]);

            if ($results !== 1) {
                throw new NoParentConfigurationFoundException(trans('exceptions.launchpad.rocket.must_be_child'));
            }
        }

        // TODO(dane): Once the admin UI is done being reworked and this is exposed
        //  in said UI, remove this so that you can actually update the denylist.
        unset($data['file_denylist']);

        $this->repository->withoutFreshModel()->update($rocket->id, $data);
    }
}
