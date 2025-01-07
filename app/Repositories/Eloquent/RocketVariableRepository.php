<?php

namespace Kubectyl\Repositories\Eloquent;

use Illuminate\Support\Collection;
use Kubectyl\Models\RocketVariable;
use Kubectyl\Contracts\Repository\RocketVariableRepositoryInterface;

class RocketVariableRepository extends EloquentRepository implements RocketVariableRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return RocketVariable::class;
    }

    /**
     * Return editable variables for a given rocket. Editable variables must be set to
     * user viewable in order to be picked up by this function.
     */
    public function getEditableVariables(int $rocket): Collection
    {
        return $this->getBuilder()->where([
            ['rocket_id', '=', $rocket],
            ['user_viewable', '=', 1],
            ['user_editable', '=', 1],
        ])->get($this->getColumns());
    }
}
