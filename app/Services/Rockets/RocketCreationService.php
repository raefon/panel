<?php

namespace Kubectyl\Services\Rockets;

use Ramsey\Uuid\Uuid;
use Kubectyl\Models\Rocket;
use Kubectyl\Contracts\Repository\RocketRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Kubectyl\Exceptions\Service\Rocket\NoParentConfigurationFoundException;

// When a mommy and a daddy pterodactyl really like each other...
class RocketCreationService
{
    /**
     * RocketCreationService constructor.
     */
    public function __construct(private ConfigRepository $config, private RocketRepositoryInterface $repository)
    {
    }

    /**
     * Create a new service option and assign it to the given service.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Kubectyl\Exceptions\Service\Rocket\NoParentConfigurationFoundException
     */
    public function handle(array $data): Rocket
    {
        $data['config_from'] = array_get($data, 'config_from');
        if (!is_null($data['config_from'])) {
            $results = $this->repository->findCountWhere([
                ['launchpad_id', '=', array_get($data, 'launchpad_id')],
                ['id', '=', array_get($data, 'config_from')],
            ]);

            if ($results !== 1) {
                throw new NoParentConfigurationFoundException(trans('exceptions.launchpad.rocket.must_be_child'));
            }
        }

        return $this->repository->create(array_merge($data, [
            'uuid' => Uuid::uuid4()->toString(),
            'author' => $this->config->get('kubectyl.service.author'),
        ]), true, true);
    }
}
