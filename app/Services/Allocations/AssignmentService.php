<?php

namespace Kubectyl\Services\Allocations;

use IPTools\Network;
use Kubectyl\Models\Cluster;
use Kubectyl\Exceptions\DisplayException;
use Illuminate\Database\ConnectionInterface;
use Kubectyl\Contracts\Repository\AllocationRepositoryInterface;
use Kubectyl\Exceptions\Service\Allocation\CidrOutOfRangeException;
use Kubectyl\Exceptions\Service\Allocation\PortOutOfRangeException;
use Kubectyl\Exceptions\Service\Allocation\InvalidPortMappingException;
use Kubectyl\Exceptions\Service\Allocation\TooManyPortsInRangeException;

class AssignmentService
{
    public const CIDR_MAX_BITS = 27;
    public const CIDR_MIN_BITS = 32;
    public const PORT_FLOOR = 1024;
    public const PORT_CEIL = 65535;
    public const PORT_RANGE_LIMIT = 250;
    public const PORT_RANGE_REGEX = '/^(\d{4,5})-(\d{4,5})$/';

    /**
     * AssignmentService constructor.
     */
    public function __construct(protected AllocationRepositoryInterface $repository, protected ConnectionInterface $connection)
    {
    }

    /**
     * Insert allocations into the database and link them to a specific cluster.
     *
     * @throws \Kubectyl\Exceptions\DisplayException
     * @throws \Kubectyl\Exceptions\Service\Allocation\CidrOutOfRangeException
     * @throws \Kubectyl\Exceptions\Service\Allocation\InvalidPortMappingException
     * @throws \Kubectyl\Exceptions\Service\Allocation\PortOutOfRangeException
     * @throws \Kubectyl\Exceptions\Service\Allocation\TooManyPortsInRangeException
     */
    public function handle(Cluster $cluster, array $data): void
    {
        $explode = explode('/', $data['allocation_ip']);
        if (count($explode) !== 1) {
            if (!ctype_digit($explode[1]) || ($explode[1] > self::CIDR_MIN_BITS || $explode[1] < self::CIDR_MAX_BITS)) {
                throw new CidrOutOfRangeException();
            }
        }

        try {
            // TODO: how should we approach supporting IPv6 with this?
            // gethostbyname only supports IPv4, but the alternative (dns_get_record) returns
            // an array of records, which is not ideal for this use case, we need a SINGLE
            // IP to use, not multiple.
            $underlying = gethostbyname($data['allocation_ip']);
            $parsed = Network::parse($underlying);
        } catch (\Exception $exception) {
            /* @noinspection PhpUndefinedVariableInspection */
            throw new DisplayException("Could not parse provided allocation IP address ({$underlying}): {$exception->getMessage()}", $exception);
        }

        $this->connection->beginTransaction();
        foreach ($parsed as $ip) {
            foreach ($data['allocation_ports'] as $port) {
                if (!is_digit($port) && !preg_match(self::PORT_RANGE_REGEX, $port)) {
                    throw new InvalidPortMappingException($port);
                }

                $insertData = [];
                if (preg_match(self::PORT_RANGE_REGEX, $port, $matches)) {
                    $block = range($matches[1], $matches[2]);

                    if (count($block) > self::PORT_RANGE_LIMIT) {
                        throw new TooManyPortsInRangeException();
                    }

                    if ((int) $matches[1] <= self::PORT_FLOOR || (int) $matches[2] > self::PORT_CEIL) {
                        throw new PortOutOfRangeException();
                    }

                    foreach ($block as $unit) {
                        $insertData[] = [
                            'cluster_id' => $cluster->id,
                            'ip' => $ip->__toString(),
                            'port' => (int) $unit,
                            'ip_alias' => array_get($data, 'allocation_alias'),
                            'server_id' => null,
                        ];
                    }
                } else {
                    if ((int) $port <= self::PORT_FLOOR || (int) $port > self::PORT_CEIL) {
                        throw new PortOutOfRangeException();
                    }

                    $insertData[] = [
                        'cluster_id' => $cluster->id,
                        'ip' => $ip->__toString(),
                        'port' => (int) $port,
                        'ip_alias' => array_get($data, 'allocation_alias'),
                        'server_id' => null,
                    ];
                }

                $this->repository->insertIgnore($insertData);
            }
        }

        $this->connection->commit();
    }
}
