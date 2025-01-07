<?php

namespace Kubectyl\Repositories\Kuber;

use Kubectyl\Models\Cluster;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\TransferException;
use Kubectyl\Exceptions\Http\Connection\DaemonConnectionException;

class DaemonConfigurationRepository extends DaemonRepository
{
    /**
     * Returns system information from the kuber instance.
     *
     * @throws \Kubectyl\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function getSystemInformation(?int $version = null): array
    {
        try {
            $response = $this->getHttpClient()->get('/api/system' . (!is_null($version) ? '?v=' . $version : ''));
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }

        return json_decode($response->getBody()->__toString(), true);
    }

    /**
     * Updates the configuration information for a daemon. Updates the information for
     * this instance using a passed-in model. This allows us to change plenty of information
     * in the model, and still use the old, pre-update model to actually make the HTTP request.
     *
     * @throws \Kubectyl\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function update(Cluster $cluster): ResponseInterface
    {
        try {
            return $this->getHttpClient()->post(
                '/api/update',
                ['json' => $cluster->getConfiguration()]
            );
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }
    }
}
