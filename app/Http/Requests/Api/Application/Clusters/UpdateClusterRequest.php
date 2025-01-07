<?php

namespace Kubectyl\Http\Requests\Api\Application\Clusters;

use Kubectyl\Models\Cluster;

class UpdateClusterRequest extends StoreClusterRequest
{
    /**
     * Apply validation rules to this request. Uses the parent class rules()
     * function but passes in the rules for updating rather than creating.
     */
    public function rules(array $rules = null): array
    {
        $cluster = $this->route()->parameter('cluster')->id;

        return parent::rules(Cluster::getRulesForUpdate($cluster));
    }
}
