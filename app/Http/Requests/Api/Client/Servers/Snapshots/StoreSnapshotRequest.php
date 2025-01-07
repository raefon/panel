<?php

namespace Kubectyl\Http\Requests\Api\Client\Servers\Snapshots;

use Kubectyl\Models\Permission;
use Kubectyl\Http\Requests\Api\Client\ClientApiRequest;

class StoreSnapshotRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SNAPSHOT_CREATE;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:191',
            'is_locked' => 'nullable|boolean',
            'ignored' => 'nullable|string',
        ];
    }
}
