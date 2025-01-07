<?php

namespace Kubectyl\Exceptions\Service\Allocation;

use Kubectyl\Exceptions\DisplayException;

class NoAutoAllocationSpaceAvailableException extends DisplayException
{
    /**
     * NoAutoAllocationSpaceAvailableException constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'Cannot assign additional allocation: no more space available on cluster.'
        );
    }
}
