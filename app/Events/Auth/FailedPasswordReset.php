<?php

namespace Kubectyl\Events\Auth;

use Kubectyl\Events\Event;
use Illuminate\Queue\SerializesModels;

class FailedPasswordReset extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public string $ip, public string $email)
    {
    }
}
