<?php

namespace Kubectyl\Events\Subuser;

use Kubectyl\Events\Event;
use Kubectyl\Models\Subuser;
use Illuminate\Queue\SerializesModels;

class Deleting extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Subuser $subuser)
    {
    }
}
