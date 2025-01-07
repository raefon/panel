<?php

namespace Kubectyl\Contracts\Repository;

use Kubectyl\Models\Rocket;
use Illuminate\Database\Eloquent\Collection;

interface RocketRepositoryInterface extends RepositoryInterface
{
    /**
     * Return an rocket with the variables relation attached.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithVariables(int $id): Rocket;

    /**
     * Return all rockets and their relations to be used in the daemon API.
     */
    public function getAllWithCopyAttributes(): Collection;

    /**
     * Return an rocket with the scriptFrom and configFrom relations loaded onto the model.
     */
    public function getWithCopyAttributes(int|string $value, string $column = 'id'): Rocket;

    /**
     * Return all the data needed to export a service.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithExportAttributes(int $id): Rocket;

    /**
     * Confirm a copy script belongs to the same launchpad as the item trying to use it.
     */
    public function isCopyableScript(int $copyFromId, int $service): bool;
}
