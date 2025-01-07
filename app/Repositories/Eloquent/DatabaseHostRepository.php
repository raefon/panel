<?php

namespace Kubectyl\Repositories\Eloquent;

use Kubectyl\Models\DatabaseHost;
use Illuminate\Support\Collection;
use Kubectyl\Contracts\Repository\DatabaseHostRepositoryInterface;

class DatabaseHostRepository extends EloquentRepository implements DatabaseHostRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return DatabaseHost::class;
    }

    /**
     * Return database hosts with a count of databases and the cluster
     * information for which it is attached.
     */
    public function getWithViewDetails(): Collection
    {
        return $this->getBuilder()->withCount('databases')->with('cluster')->get();
    }
}
