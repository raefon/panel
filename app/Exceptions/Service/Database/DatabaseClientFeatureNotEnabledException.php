<?php

namespace Kubectyl\Exceptions\Service\Database;

use Kubectyl\Exceptions\KubectylException;

class DatabaseClientFeatureNotEnabledException extends KubectylException
{
    public function __construct()
    {
        parent::__construct('Client database creation is not enabled in this Panel.');
    }
}
