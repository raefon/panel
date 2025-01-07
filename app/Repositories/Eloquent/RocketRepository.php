<?php

namespace Kubectyl\Repositories\Eloquent;

use Kubectyl\Models\Rocket;
use Webmozart\Assert\Assert;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Kubectyl\Exceptions\Repository\RecordNotFoundException;
use Kubectyl\Contracts\Repository\RocketRepositoryInterface;

class RocketRepository extends EloquentRepository implements RocketRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return Rocket::class;
    }

    /**
     * Return an rocket with the variables relation attached.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithVariables(int $id): Rocket
    {
        try {
            return $this->getBuilder()->with('variables')->findOrFail($id, $this->getColumns());
        } catch (ModelNotFoundException) {
            throw new RecordNotFoundException();
        }
    }

    /**
     * Return all rockets and their relations to be used in the daemon API.
     */
    public function getAllWithCopyAttributes(): Collection
    {
        return $this->getBuilder()->with('scriptFrom', 'configFrom')->get($this->getColumns());
    }

    /**
     * Return an rocket with the scriptFrom and configFrom relations loaded onto the model.
     *
     * @param int|string $value
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithCopyAttributes($value, string $column = 'id'): Rocket
    {
        Assert::true(is_digit($value) || is_string($value), 'First argument passed to getWithCopyAttributes must be an integer or string, received %s.');

        try {
            return $this->getBuilder()->with('scriptFrom', 'configFrom')->where($column, '=', $value)->firstOrFail($this->getColumns());
        } catch (ModelNotFoundException) {
            throw new RecordNotFoundException();
        }
    }

    /**
     * Return all the data needed to export a service.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithExportAttributes(int $id): Rocket
    {
        try {
            return $this->getBuilder()->with('scriptFrom', 'configFrom', 'variables')->findOrFail($id, $this->getColumns());
        } catch (ModelNotFoundException) {
            throw new RecordNotFoundException();
        }
    }

    /**
     * Confirm a copy script belongs to the same launchpad as the item trying to use it.
     */
    public function isCopyableScript(int $copyFromId, int $service): bool
    {
        return $this->getBuilder()->whereNull('copy_script_from')
            ->where('id', '=', $copyFromId)
            ->where('launchpad_id', '=', $service)
            ->exists();
    }
}
