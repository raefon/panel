<?php

namespace Kubectyl\Exceptions\Service\Location;

use Illuminate\Http\Response;
use Kubectyl\Exceptions\DisplayException;

class HasActiveClustersException extends DisplayException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
