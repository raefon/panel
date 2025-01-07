<?php

namespace Kubectyl\Exceptions\Service;

use Illuminate\Http\Response;
use Kubectyl\Exceptions\DisplayException;

class HasActiveServersException extends DisplayException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
