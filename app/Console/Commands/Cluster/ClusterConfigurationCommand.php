<?php

namespace Kubectyl\Console\Commands\Cluster;

use Kubectyl\Models\Cluster;
use Illuminate\Console\Command;

class ClusterConfigurationCommand extends Command
{
    protected $signature = 'p:cluster:configuration
                            {cluster : The ID or UUID of the cluster to return the configuration for.}
                            {--format=yaml : The output format. Options are "yaml" and "json".}';

    protected $description = 'Displays the configuration for the specified cluster.';

    public function handle(): int
    {
        $column = ctype_digit((string) $this->argument('cluster')) ? 'id' : 'uuid';

        /** @var \Kubectyl\Models\Cluster $cluster */
        $cluster = Cluster::query()->where($column, $this->argument('cluster'))->firstOr(function () {
            $this->error('The selected cluster does not exist.');

            exit(1);
        });

        $format = $this->option('format');
        if (!in_array($format, ['yaml', 'yml', 'json'])) {
            $this->error('Invalid format specified. Valid options are "yaml" and "json".');

            return 1;
        }

        if ($format === 'json') {
            $this->output->write($cluster->getJsonConfiguration(true));
        } else {
            $this->output->write($cluster->getYamlConfiguration());
        }

        $this->output->newLine();

        return 0;
    }
}
