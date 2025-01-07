<?php

namespace Kubectyl\Transformers\Api\Client;

use Kubectyl\Models\Snapshot;

class SnapshotTransformer extends BaseClientTransformer
{
    public function getResourceName(): string
    {
        return Snapshot::RESOURCE_NAME;
    }

    public function transform(Snapshot $snapshot): array
    {
        return [
            'uuid' => $snapshot->uuid,
            'is_successful' => $snapshot->is_successful,
            'is_locked' => $snapshot->is_locked,
            'name' => $snapshot->name,
            'snapcontent' => $snapshot->snapcontent,
            'bytes' => $snapshot->bytes,
            'created_at' => $snapshot->created_at->toAtomString(),
            'completed_at' => $snapshot->completed_at ? $snapshot->completed_at->toAtomString() : null,
        ];
    }
}
