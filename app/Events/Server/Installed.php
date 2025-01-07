<?php

namespace Kubectyl\Events\Server;

use Kubectyl\Events\Event;
use Kubectyl\Models\Server;
use Illuminate\Queue\SerializesModels;

class Installed extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Server $server)
    {
    }
}
