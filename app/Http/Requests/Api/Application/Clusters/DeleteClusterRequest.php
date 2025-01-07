<?php

namespace Kubectyl\Http\Requests\Api\Application\Clusters;

use Kubectyl\Services\Acl\Api\AdminAcl;
use Kubectyl\Http\Requests\Api\Application\ApplicationApiRequest;

class DeleteClusterRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_CLUSTERS;

    protected int $permission = AdminAcl::WRITE;
}
