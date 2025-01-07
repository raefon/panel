<?php

namespace Kubectyl\Services\Launchpads;

use Ramsey\Uuid\Uuid;
use Kubectyl\Models\Launchpad;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface;

class LaunchpadCreationService
{
    /**
     * LaunchpadCreationService constructor.
     */
    public function __construct(private ConfigRepository $config, private LaunchpadRepositoryInterface $repository)
    {
    }

    /**
     * Create a new nest on the system.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    public function handle(array $data, string $author = null): Launchpad
    {
        return $this->repository->create([
            'uuid' => Uuid::uuid4()->toString(),
            'author' => $author ?? $this->config->get('kubectyl.service.author'),
            'name' => array_get($data, 'name'),
            'description' => array_get($data, 'description'),
        ], true, true);
    }
}
