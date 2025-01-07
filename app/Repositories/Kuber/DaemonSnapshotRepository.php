<?php

namespace Kubectyl\Repositories\Kuber;

use Kubectyl\Models\Server;
use Webmozart\Assert\Assert;
use Kubectyl\Models\Snapshot;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\TransferException;
use Kubectyl\Exceptions\Http\Connection\DaemonConnectionException;

class DaemonSnapshotRepository extends DaemonRepository
{
    protected ?string $adapter;

    /**
     * Sets the snapshot adapter for this execution instance.
     */
    public function setSnapshotAdapter(string $adapter): self
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Tells the remote Daemon to begin generating a snapshot for the server.
     *
     * @throws \Kubectyl\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function snapshot(Snapshot $snapshot): ResponseInterface
    {
        Assert::isInstanceOf($this->server, Server::class);

        try {
            return $this->getHttpClient()->post(
                sprintf('/api/servers/%s/snapshot', $this->server->uuid),
                [
                    'json' => [
                        'adapter' => $this->adapter ?? config('snapshots.default'),
                        'uuid' => $snapshot->uuid,
                    ],
                ]
            );
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }
    }

    /**
     * Sends a request to Kuber to begin restoring a snapshot for a server.
     *
     * @throws \Kubectyl\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function restore(Snapshot $snapshot, string $url = null, bool $truncate = false): ResponseInterface
    {
        Assert::isInstanceOf($this->server, Server::class);

        try {
            return $this->getHttpClient()->post(
                sprintf('/api/servers/%s/snapshot/%s/restore', $this->server->uuid, $snapshot->uuid),
                [
                    'json' => [
                        'adapter' => $snapshot->disk,
                        'truncate_directory' => $truncate,
                        'download_url' => $url ?? '',
                    ],
                ]
            );
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }
    }

    /**
     * Deletes a snapshot from the daemon.
     *
     * @throws \Kubectyl\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function delete(Snapshot $snapshot): ResponseInterface
    {
        Assert::isInstanceOf($this->server, Server::class);

        try {
            return $this->getHttpClient()->delete(
                sprintf('/api/servers/%s/snapshot/%s', $this->server->uuid, $snapshot->uuid)
            );
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }
    }
}
