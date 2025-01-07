<?php

namespace Kubectyl\Http\Controllers\Api\Application\Clusters;

use Kubectyl\Models\Cluster;
use Kubectyl\Models\Allocation;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Kubectyl\Services\Allocations\AssignmentService;
use Kubectyl\Services\Allocations\AllocationDeletionService;
use Kubectyl\Transformers\Api\Application\AllocationTransformer;
use Kubectyl\Http\Controllers\Api\Application\ApplicationApiController;
use Kubectyl\Http\Requests\Api\Application\Allocations\GetAllocationsRequest;
use Kubectyl\Http\Requests\Api\Application\Allocations\StoreAllocationRequest;
use Kubectyl\Http\Requests\Api\Application\Allocations\DeleteAllocationRequest;

class AllocationController extends ApplicationApiController
{
    /**
     * AllocationController constructor.
     */
    public function __construct(
        private AssignmentService $assignmentService,
        private AllocationDeletionService $deletionService
    ) {
        parent::__construct();
    }

    /**
     * Return all the allocations that exist for a given cluster.
     */
    public function index(GetAllocationsRequest $request, Cluster $cluster): array
    {
        $allocations = QueryBuilder::for($cluster->allocations())
            ->allowedFilters([
                AllowedFilter::exact('ip'),
                AllowedFilter::exact('port'),
                'ip_alias',
                AllowedFilter::callback('server_id', function (Builder $builder, $value) {
                    if (empty($value) || is_bool($value) || !ctype_digit((string) $value)) {
                        return $builder->whereNull('server_id');
                    }

                    return $builder->where('server_id', $value);
                }),
            ])
            ->paginate($request->query('per_page') ?? 50);

        return $this->fractal->collection($allocations)
            ->transformWith($this->getTransformer(AllocationTransformer::class))
            ->toArray();
    }

    /**
     * Store new allocations for a given cluster.
     *
     * @throws \Kubectyl\Exceptions\DisplayException
     * @throws \Kubectyl\Exceptions\Service\Allocation\CidrOutOfRangeException
     * @throws \Kubectyl\Exceptions\Service\Allocation\InvalidPortMappingException
     * @throws \Kubectyl\Exceptions\Service\Allocation\PortOutOfRangeException
     * @throws \Kubectyl\Exceptions\Service\Allocation\TooManyPortsInRangeException
     */
    public function store(StoreAllocationRequest $request, Cluster $cluster): JsonResponse
    {
        $this->assignmentService->handle($cluster, $request->validated());

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Delete a specific allocation from the Panel.
     *
     * @throws \Kubectyl\Exceptions\Service\Allocation\ServerUsingAllocationException
     */
    public function delete(DeleteAllocationRequest $request, Cluster $cluster, Allocation $allocation): JsonResponse
    {
        $this->deletionService->handle($allocation);

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
