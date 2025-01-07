<?php

namespace Kubectyl\Repositories\Eloquent;

use Kubectyl\Models\ServerVariable;
use Kubectyl\Contracts\Repository\ServerVariableRepositoryInterface;

class ServerVariableRepository extends EloquentRepository implements ServerVariableRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return ServerVariable::class;
    }
}
