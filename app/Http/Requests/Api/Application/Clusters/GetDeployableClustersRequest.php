<?php

namespace Kubectyl\Http\Requests\Api\Application\Clusters;

class GetDeployableClustersRequest extends GetClustersRequest
{
    public function rules(): array
    {
        return [
            'page' => 'integer',
            'location_ids' => 'array',
            'location_ids.*' => 'integer',
        ];
    }
}
