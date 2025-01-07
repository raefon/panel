<?php

namespace Kubectyl\Repositories\Eloquent;

use Kubectyl\Models\Cluster;
use Illuminate\Support\Collection;
use Kubectyl\Contracts\Repository\ClusterRepositoryInterface;

class ClusterRepository extends EloquentRepository implements ClusterRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return Cluster::class;
    }

    /**
     * Return a single cluster with location and server information.
     */
    public function loadLocationAndServerCount(Cluster $cluster, bool $refresh = false): Cluster
    {
        if (!$cluster->relationLoaded('location') || $refresh) {
            $cluster->load('location');
        }

        // This is quite ugly and can probably be improved down the road.
        // And by probably, I mean it should.
        if (is_null($cluster->servers_count) || $refresh) {
            $cluster->load('servers');
            $cluster->setRelation('servers_count', count($cluster->getRelation('servers')));
            unset($cluster->servers);
        }

        return $cluster;
    }

    /**
     * Attach a paginated set of allocations to a cluster mode including
     * any servers that are also attached to those allocations.
     */
    public function loadClusterAllocations(Cluster $cluster, bool $refresh = false): Cluster
    {
        $cluster->setRelation(
            'allocations',
            $cluster->allocations()
                ->orderByRaw('server_id IS NOT NULL DESC, server_id IS NULL')
                ->orderByRaw('INET_ATON(ip) ASC')
                ->orderBy('port')
                ->with('server:id,name')
                ->paginate(50)
        );

        return $cluster;
    }

    /**
     * Return a collection of clusters for all locations to use in server creation UI.
     */
    public function getClustersForServerCreation(): Collection
    {
        return $this->getBuilder()->with('allocations')->get()->map(function (Cluster $item) {
            $filtered = $item->getRelation('allocations')->where('server_id', null)->map(function ($map) {
                return collect($map)->only(['id', 'ip', 'port']);
            });

            $item->ports = $filtered->map(function ($map) {
                return [
                    'id' => $map['id'],
                    'text' => sprintf('%s:%s', $map['ip'], $map['port']),
                ];
            })->values();

            return [
                'id' => $item->id,
                'text' => $item->name,
                'allocations' => $item->ports,
            ];
        })->values();
    }
}
