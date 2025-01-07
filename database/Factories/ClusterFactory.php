<?php

namespace Database\Factories;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Kubectyl\Models\Cluster;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClusterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cluster::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'public' => true,
            'name' => 'FactoryCluster_' . Str::random(10),
            'fqdn' => $this->faker->unique()->ipv4,
            'scheme' => 'http',
            'behind_proxy' => false,
            'upload_size' => 100,
            'daemon_token_id' => Str::random(Cluster::DAEMON_TOKEN_ID_LENGTH),
            'daemon_token' => Crypt::encrypt(Str::random(Cluster::DAEMON_TOKEN_LENGTH)),
            'daemonListen' => 8080,
            'daemonBase' => '/var/lib/kubectyl/volumes',
            'host' => '127.0.0.1:6443',
            'bearer_token' => 'test',
            'insecure' => true,
            'service_type' => 'nodeport',
            'storage_class' => 'manual',
            'ns' => 'default',
            'sftp_image' => 'ghcr.io/kubectyl/sftp-server:latest',
            'sftp_port' => 2022,
            'maintenance_mode' => false,
            'dns_policy' => 'clusterfirst',
            'image_pull_policy' => 'ifnotpresent',
            'metallb_shared_ip' => true,
            'external_traffic_policy' => 'cluster',
            'metrics' => 'metrics_api',
            'snapshot_class' => 'csi-rbd-provisioner',
        ];
    }
}
