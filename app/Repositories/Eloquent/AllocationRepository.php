<?php

namespace Kubectyl\Repositories\Eloquent;

use Kubectyl\Models\Allocation;
use Illuminate\Database\Eloquent\Builder;
use Kubectyl\Contracts\Repository\AllocationRepositoryInterface;

class AllocationRepository extends EloquentRepository implements AllocationRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return Allocation::class;
    }

    /**
     * Return all the allocations that exist for a cluster that are not currently
     * allocated.
     */
    public function getUnassignedAllocationIds(int $cluster): array
    {
        return Allocation::query()->select('id')
            ->whereNull('server_id')
            ->where('cluster_id', $cluster)
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * Return a concatenated result set of cluster ips that already have at least one
     * server assigned to that IP. This allows for filtering out sets for
     * dedicated allocation IPs.
     *
     * If an array of clusters is passed the results will be limited to allocations
     * in those clusters.
     */
    protected function getDiscardableDedicatedAllocations(array $clusters = []): array
    {
        $query = Allocation::query()->selectRaw('CONCAT_WS("-", cluster_id, ip) as result');

        if (!empty($clusters)) {
            $query->whereIn('cluster_id', $clusters);
        }

        return $query->whereNotNull('server_id')
            ->groupByRaw('CONCAT(cluster_id, ip)')
            ->get()
            ->pluck('result')
            ->toArray();
    }

    /**
     * Return a single allocation from those meeting the requirements.
     */
    public function getRandomAllocation(array $clusters, array $ports, bool $dedicated = false): ?Allocation
    {
        $query = Allocation::query()->whereNull('server_id');

        if (!empty($clusters)) {
            $query->whereIn('cluster_id', $clusters);
        }

        if (!empty($ports)) {
            $query->where(function (Builder $inner) use ($ports) {
                $whereIn = [];
                foreach ($ports as $port) {
                    if (is_array($port)) {
                        $inner->orWhereBetween('port', $port);
                        continue;
                    }

                    $whereIn[] = $port;
                }

                if (!empty($whereIn)) {
                    $inner->orWhereIn('port', $whereIn);
                }
            });
        }

        // If this allocation should not be shared with any other servers get
        // the data and modify the query as necessary,
        if ($dedicated) {
            $discard = $this->getDiscardableDedicatedAllocations($clusters);

            if (!empty($discard)) {
                $query->whereNotIn(
                    $this->getBuilder()->raw('CONCAT_WS("-", cluster_id, ip)'),
                    $discard
                );
            }
        }

        return $query->inRandomOrder()->first();
    }
}
