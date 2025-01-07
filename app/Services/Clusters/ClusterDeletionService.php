<?php

namespace Kubectyl\Services\Clusters;

use Kubectyl\Models\Cluster;
use Illuminate\Contracts\Translation\Translator;
use Kubectyl\Exceptions\Service\HasActiveServersException;
use Kubectyl\Contracts\Repository\ServerRepositoryInterface;
use Kubectyl\Contracts\Repository\ClusterRepositoryInterface;

class ClusterDeletionService
{
    /**
     * ClusterDeletionService constructor.
     */
    public function __construct(
        protected ClusterRepositoryInterface $repository,
        protected ServerRepositoryInterface $serverRepository,
        protected Translator $translator
    ) {
    }

    /**
     * Delete a cluster from the panel if no servers are attached to it.
     *
     * @throws \Kubectyl\Exceptions\Service\HasActiveServersException
     */
    public function handle(int|Cluster $cluster): int
    {
        if ($cluster instanceof Cluster) {
            $cluster = $cluster->id;
        }

        $servers = $this->serverRepository->setColumns('id')->findCountWhere([['cluster_id', '=', $cluster]]);
        if ($servers > 0) {
            throw new HasActiveServersException($this->translator->get('exceptions.cluster.servers_attached'));
        }

        return $this->repository->delete($cluster);
    }
}
