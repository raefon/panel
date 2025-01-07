<?php

namespace Kubectyl\Repositories\Eloquent;

use Kubectyl\Models\RecoveryToken;

class RecoveryTokenRepository extends EloquentRepository
{
    public function model(): string
    {
        return RecoveryToken::class;
    }
}
