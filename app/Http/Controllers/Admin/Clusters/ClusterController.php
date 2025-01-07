<?php

namespace Kubectyl\Http\Controllers\Admin\Clusters;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Kubectyl\Models\Cluster;
use Spatie\QueryBuilder\QueryBuilder;
use Kubectyl\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory as ViewFactory;

class ClusterController extends Controller
{
    /**
     * NodeController constructor.
     */
    public function __construct(private ViewFactory $view)
    {
    }

    /**
     * Returns a listing of nodes on the system.
     */
    public function index(Request $request): View
    {
        $clusters = QueryBuilder::for(
            Cluster::query()->with('location')->withCount('servers')
        )
            ->allowedFilters(['uuid', 'name'])
            ->allowedSorts(['id'])
            ->paginate(25);

        return $this->view->make('admin.clusters.index', ['clusters' => $clusters]);
    }
}
