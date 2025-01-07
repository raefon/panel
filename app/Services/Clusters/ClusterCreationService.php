<?php

namespace Kubectyl\Services\Clusters;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Kubectyl\Models\Cluster;
use Illuminate\Contracts\Encryption\Encrypter;
use Kubectyl\Contracts\Repository\ClusterRepositoryInterface;

class ClusterCreationService
{
    /**
     * ClusterCreationService constructor.
     */
    public function __construct(protected ClusterRepositoryInterface $repository)
    {
    }

    /**
     * Create a new cluster on the panel.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    public function handle(array $data): Cluster
    {
        $data['uuid'] = Uuid::uuid4()->toString();
        $data['daemon_token'] = app(Encrypter::class)->encrypt(Str::random(Cluster::DAEMON_TOKEN_LENGTH));
        $data['bearer_token'] = app(Encrypter::class)->encrypt($data['bearer_token']);
        $data['daemon_token_id'] = Str::random(Cluster::DAEMON_TOKEN_ID_LENGTH);

        return $this->repository->create($data, true, true);
    }
}
