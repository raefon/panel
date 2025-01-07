<?php

namespace Kubectyl\Repositories\Kuber;

use GuzzleHttp\Client;
use Kubectyl\Models\Server;
use Kubectyl\Models\Cluster;
use Webmozart\Assert\Assert;
use Illuminate\Contracts\Foundation\Application;

abstract class DaemonRepository
{
    protected ?Server $server;

    protected ?Cluster $cluster;

    /**
     * DaemonRepository constructor.
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * Set the server model this request is stemming from.
     */
    public function setServer(Server $server): self
    {
        $this->server = $server;

        $this->setCluster($this->server->cluster);

        return $this;
    }

    /**
     * Set the cluster model this request is stemming from.
     */
    public function setCluster(Cluster $cluster): self
    {
        $this->cluster = $cluster;

        return $this;
    }

    /**
     * Return an instance of the Guzzle HTTP Client to be used for requests.
     */
    public function getHttpClient(array $headers = []): Client
    {
        Assert::isInstanceOf($this->cluster, Cluster::class);

        return new Client([
            'verify' => $this->app->environment('production'),
            'base_uri' => $this->cluster->getConnectionAddress(),
            'timeout' => config('kubectyl.guzzle.timeout'),
            'connect_timeout' => config('kubectyl.guzzle.connect_timeout'),
            'headers' => array_merge($headers, [
                'Authorization' => 'Bearer ' . $this->cluster->getDecryptedKey(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]),
        ]);
    }
}
