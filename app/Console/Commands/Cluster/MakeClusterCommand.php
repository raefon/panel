<?php

namespace Kubectyl\Console\Commands\Cluster;

use Illuminate\Console\Command;
use Kubectyl\Services\Clusters\ClusterCreationService;

class MakeClusterCommand extends Command
{
    protected $signature = 'p:cluster:make
                            {--name= : A name to identify the cluster.}
                            {--description= : A description to identify the cluster.}
                            {--locationId= : A valid locationId.}
                            {--fqdn= : The domain name (e.g kuber.example.com) to be used for connecting to the daemon. An IP address may only be used if you are not using SSL for this daemon.}
                            {--public= : Should the cluster be public or private? (public=1 / private=0).}
                            {--scheme= : Which scheme should be used? (Enable SSL=https / Disable SSL=http).}
                            {--proxy= : Is the daemon behind a proxy? (Yes=1 / No=0).}
                            {--maintenance= : Should maintenance mode be enabled? (Enable Maintenance mode=1 / Disable Maintenance mode=0).}
                            {--uploadSize= : Enter the maximum upload filesize.}
                            {--daemonListeningPort= : Enter the kuber listening port.}
                            {--daemonBase= : Enter the base folder.}
                            {--host= : Test}
                            {--bearer_token= : Test}
                            {--insecure= : Test}
                            {--service_type= : Test}
                            {--storage_class= : Test}
                            {--namespace= : Test}
                            {--metrics= : Test}
                            {--snapshot_class= : Test}
                            {--external_traffic_policy= : Test}';

    protected $description = 'Creates a new cluster on the system via the CLI.';

    /**
     * MakeClusterCommand constructor.
     */
    public function __construct(private ClusterCreationService $creationService)
    {
        parent::__construct();
    }

    /**
     * Handle the command execution process.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    public function handle()
    {
        $data['name'] = $this->option('name') ?? $this->ask('Enter a short identifier used to distinguish this cluster from others');
        $data['description'] = $this->option('description') ?? $this->ask('Enter a description to identify the cluster');
        $data['location_id'] = $this->option('locationId') ?? $this->ask('Enter a valid location id');
        $data['scheme'] = $this->option('scheme') ?? $this->anticipate(
            'Please either enter https for SSL or http for a non-ssl connection',
            ['https', 'http'],
            'https'
        );
        $data['fqdn'] = $this->option('fqdn') ?? $this->ask('Enter a domain name (e.g kuber.example.com) to be used for connecting to the daemon. An IP address may only be used if you are not using SSL for this daemon');
        $data['public'] = $this->option('public') ?? $this->confirm('Should this cluster be public? As a note, setting a cluster to private you will be denying the ability to auto-deploy to this cluster.', true);
        $data['behind_proxy'] = $this->option('proxy') ?? $this->confirm('Is your FQDN behind a proxy?');
        $data['maintenance_mode'] = $this->option('maintenance') ?? $this->confirm('Should maintenance mode be enabled?');
        $data['upload_size'] = $this->option('uploadSize') ?? $this->ask('Enter the maximum filesize upload', '100');
        $data['daemonListen'] = $this->option('daemonListeningPort') ?? $this->ask('Enter the wings listening port', '8080');
        $data['daemonBase'] = $this->option('daemonBase') ?? $this->ask('Enter the base folder', '/var/lib/kubectyl/volumes');
        $data['host'] = $this->option('host') ?? $this->ask('Enter the host');
        $data['bearer_token'] = $this->option('bearer_token') ?? $this->ask('Enter the bearer_token');
        $data['insecure'] = $this->option('insecure') ?? $this->ask('Enter the insecure');
        $data['service_type'] = $this->option('service_type') ?? $this->ask('Enter the service_type');
        $data['storage_class'] = $this->option('storage_class') ?? $this->ask('Enter the storage_class');
        $data['ns'] = $this->option('namespace') ?? $this->ask('Enter the ns');
        $data['metrics'] = $this->option('metrics') ?? $this->ask('Enter the metrics');
        $data['snapshot_class'] = $this->option('snapshot_class') ?? $this->ask('Enter the snapshot class');
        $data['external_traffic_policy'] = $this->option('external_traffic_policy') ?? $this->ask('Enter the external traffic policy');

        $cluster = $this->creationService->handle($data);
        $this->line('Successfully created a new cluster on the location ' . $data['location_id'] . ' with the name ' . $data['name'] . ' and has an id of ' . $cluster->id . '.');
    }
}
