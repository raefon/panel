<?php

namespace Kubectyl\Contracts\Repository;

use Illuminate\Support\Collection;

interface RocketVariableRepositoryInterface extends RepositoryInterface
{
    /**
     * Return editable variables for a given rocket. Editable variables must be set to
     * user viewable in order to be picked up by this function.
     */
    public function getEditableVariables(int $rocket): Collection;
}
