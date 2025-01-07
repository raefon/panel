<?php

namespace Kubectyl\Repositories\Eloquent;

use Kubectyl\Models\User;
use Kubectyl\Contracts\Repository\UserRepositoryInterface;

class UserRepository extends EloquentRepository implements UserRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return User::class;
    }
}
