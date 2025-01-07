<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Kubectyl\Services\Launchpads\LaunchpadCreationService;
use Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface;

class LaunchpadSeeder extends Seeder
{
    /**
     * @var \Kubectyl\Services\Launchpads\LaunchpadCreationService
     */
    private $creationService;

    /**
     * @var \Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface
     */
    private $repository;

    /**
     * LaunchpadSeeder constructor.
     */
    public function __construct(
        LaunchpadCreationService $creationService,
        LaunchpadRepositoryInterface $repository
    ) {
        $this->creationService = $creationService;
        $this->repository = $repository;
    }

    /**
     * Run the seeder to add missing nests to the Panel.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    public function run()
    {
        $items = $this->repository->findWhere([
            'author' => 'support@kubectyl.org',
        ])->keyBy('name')->toArray();

        $this->createMinecraftNest(array_get($items, 'Minecraft'));
        $this->createSourceEngineNest(array_get($items, 'Source Engine'));
        $this->createVoiceServersNest(array_get($items, 'Voice Servers'));
        $this->createRustNest(array_get($items, 'Rust'));
    }

    /**
     * Create the Minecraft launchpad to be used later on.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    private function createMinecraftNest(array $launchpad = null)
    {
        if (is_null($launchpad)) {
            $this->creationService->handle([
                'name' => 'Minecraft',
                'description' => 'Minecraft - the classic game from Mojang. With support for Vanilla MC, Spigot, and many others!',
            ], 'support@kubectyl.org');
        }
    }

    /**
     * Create the Source Engine Games launchpad to be used later on.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    private function createSourceEngineNest(array $launchpad = null)
    {
        if (is_null($launchpad)) {
            $this->creationService->handle([
                'name' => 'Source Engine',
                'description' => 'Includes support for most Source Dedicated Server games.',
            ], 'support@kubectyl.org');
        }
    }

    /**
     * Create the Voice Servers launchpad to be used later on.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    private function createVoiceServersNest(array $launchpad = null)
    {
        if (is_null($launchpad)) {
            $this->creationService->handle([
                'name' => 'Voice Servers',
                'description' => 'Voice servers such as Mumble and Teamspeak 3.',
            ], 'support@kubectyl.org');
        }
    }

    /**
     * Create the Rust launchpad to be used later on.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    private function createRustNest(array $launchpad = null)
    {
        if (is_null($launchpad)) {
            $this->creationService->handle([
                'name' => 'Rust',
                'description' => 'Rust - A game where you must fight to survive.',
            ], 'support@kubectyl.org');
        }
    }
}
