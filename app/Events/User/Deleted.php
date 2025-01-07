<?php

namespace Kubectyl\Events\User;

use Kubectyl\Models\User;
use Kubectyl\Events\Event;
use Illuminate\Queue\SerializesModels;

class Deleted extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $user)
    {
    }
}
