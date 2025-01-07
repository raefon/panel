<?php

namespace Kubectyl\Events\Auth;

use Kubectyl\Models\User;
use Kubectyl\Events\Event;

class ProvidedAuthenticationToken extends Event
{
    public function __construct(public User $user, public bool $recovery = false)
    {
    }
}
